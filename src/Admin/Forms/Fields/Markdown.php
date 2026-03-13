<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Forms\Fields;

class Markdown
{
    public static function script(): string
    {
        $imageLibraryUrl = admin_route('api.file-library');
        $uploadUrl       = admin_route('api.files.upload');

        return <<<JS
    $(function () {
        if (typeof editormd === "undefined") {
            console.error('EditorMD is not loaded');
            return;
        }

        // Constants
        const PAGE_SIZE = 12;
        const SEARCH_DELAY = 500;
        const IMAGE_PREVIEW_SIZE = {
            width: 150,
            height: 150
        };
        const KEY_CODES = {
            ENTER: 13,
            ESCAPE: 27,
            SPACE: 32
        };

        editormd.toolbarHandlers["image-library"] = function(cm) {
            var currentPage = 1;
            var searchTimer = null;
            var isLoading = false;
            var selectedImages = new Set();

            // Create modal template
            var dialog = createModalTemplate();

            // Image loading function
            function loadImages(page, search = '') {
                if (isLoading) return;

                setLoadingState(true);
                clearErrors();

                $.ajax({
                    url: '$imageLibraryUrl',
                    method: 'GET',
                    data: {
                        page: page,
                        page_size: PAGE_SIZE,
                        name: search,
                        sort_by: 'created_at',
                        sort_direction: 'desc'
                    },
                    success: function(response) {
                        setLoadingState(false);

                        if (!isValidResponse(response)) {
                            showError('Invalid response from server');
                            return;
                        }

                        renderImages(response.data);
                        updatePagination(response.data);
                    },
                    error: function(xhr, status, error) {
                        setLoadingState(false);
                        const errorMessage = xhr.responseJSON?.message || error || 'Unknown error';
                        showError('Failed to load images: ' + errorMessage);
                        console.error('Error loading images:', { xhr, status, error });
                    }
                });
            }

            // Helper functions
            function createModalTemplate() {
                return $('<div class="modal fade" id="image-library-modal" role="dialog" aria-labelledby="imageLibraryModalTitle">' +
                    '<div class="modal-dialog modal-lg" role="document">' +
                    '<div class="modal-content">' +
                    '<div class="modal-header">' +
                    '<h4 class="modal-title" id="imageLibraryModalTitle">Image Library</h4>' +
                    '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '</div>' +
                    '<div class="modal-body">' +
                    '<div class="form-group mb-3">' +
                    '<div class="d-flex justify-content-between align-items-center">' +
                    '<input type="text" class="form-control search-input mr-2" placeholder="Search images by name..." aria-label="Search images">' +
                    '<label class="btn btn-primary mb-0" style="white-space: nowrap;">' +
                    '<i class="fa fa-upload"></i> Upload Images' +
                    '<input type="file" multiple accept="image/*" class="d-none" id="image-upload-input">' +
                    '</label>' +
                    '</div>' +
                    '</div>' +
                    '<div class="alert alert-danger error-container" style="display: none;" role="alert"></div>' +
                    '<div class="upload-progress-container" style="display: none;">' +
                    '<div class="overall-progress mb-3">' +
                    '<div class="d-flex justify-content-between mb-1">' +
                    '<span>Overall Progress:</span>' +
                    '<span class="overall-status">0/0 files</span>' +
                    '</div>' +
                    '<div class="progress">' +
                    '<div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>' +
                    '</div>' +
                    '</div>' +
                    '<div class="individual-progress"></div>' +
                    '</div>' +
                    '<div class="image-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(' + IMAGE_PREVIEW_SIZE.width + 'px, 1fr)); gap: 10px; min-height: 400px;" role="list"></div>' +
                    '<div class="text-center loading-indicator" style="display: none; padding: 20px;">' +
                    '<i class="fa fa-spinner fa-spin fa-2x" aria-hidden="true"></i>' +
                    '<p class="mt-2">Loading images...</p>' +
                    '</div>' +
                    '<div class="pagination-controls text-center mt-3" role="navigation" aria-label="Pagination">' +
                    '<button class="btn btn-sm btn-default prev-page" disabled aria-label="Previous page"><i class="fa fa-chevron-left" aria-hidden="true"></i> Previous</button>' +
                    '<span class="mx-3 page-info">Page <span class="current-page">1</span> of <span class="total-pages">1</span></span>' +
                    '<button class="btn btn-sm btn-default next-page" disabled aria-label="Next page">Next <i class="fa fa-chevron-right" aria-hidden="true"></i></button>' +
                    '</div>' +
                    '</div>' +
                    '<div class="modal-footer">' +
                    '<span class="mr-auto selected-count" role="status" aria-live="polite">0 images selected</span>' +
                    '<button type="button" class="btn btn-primary insert-images" disabled>Insert Selected</button>' +
                    '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>');
            }

            function setLoadingState(loading) {
                isLoading = loading;
                var imageList = dialog.find('.image-list');
                var loadingIndicator = dialog.find('.loading-indicator');
                dialog.find('.search-input, .prev-page, .next-page, .insert-images').prop('disabled', loading);

                if (loading) {
                    imageList.hide().attr('aria-busy', 'true');
                    loadingIndicator.show();
                } else {
                    loadingIndicator.hide();
                    imageList.show().attr('aria-busy', 'false');
                }
            }

            function showError(message) {
                var errorContainer = dialog.find('.error-container');
                errorContainer.html(message).show().attr('aria-hidden', 'false');
            }

            function clearErrors() {
                dialog.find('.error-container').hide().attr('aria-hidden', 'true');
            }

            function isValidResponse(response) {
                return response?.status &&
                       response?.data?.data &&
                       Array.isArray(response.data.data);
            }

            function renderImages(data) {
                var imageList = dialog.find('.image-list');
                imageList.empty();

                if (!data.data.length) {
                    imageList.html('<div class="text-center w-100 p-3" role="alert">No images found</div>');
                    return;
                }

                data.data.forEach(function(file) {
                    var img = $('<div class="image-item" style="position: relative;" role="listitem" tabindex="0" aria-label="' + file.original_filename + '">' +
                        '<div class="image-preview" style="height: ' + IMAGE_PREVIEW_SIZE.height + 'px; position: relative;">' +
                        '<img src="' + file.url + '" alt="' + file.url + '" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" loading="lazy">' +
                        '<div class="image-overlay" style="display: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5);">' +
                        '<i class="fa fa-check" style="color: white; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" aria-hidden="true"></i>' +
                        '</div>' +
                        '</div>' +
                        '<div class="image-info" style="font-size: 12px; padding: 5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="' + file.original_filename + '">' +
                        file.original_filename +
                        '</div>' +
                        '</div>');

                    img.data('url', file.url);
                    img.data('full_path', file.full_path);

                    const toggleSelection = () => {
                        const isSelected = img.hasClass('selected');
                        if (isSelected) {
                            selectedImages.delete(file.url);
                        } else {
                            selectedImages.add(file.url);
                        }
                        img.toggleClass('selected')
                           .find('.image-overlay').toggle();
                        img.attr('aria-selected', !isSelected);
                        updateSelectedCount();
                    };

                    img.on('click', function(e) {
                        e.preventDefault();
                        toggleSelection();
                    });

                    img.on('keydown', function(e) {
                        if (e.keyCode === KEY_CODES.ENTER || e.keyCode === KEY_CODES.SPACE) {
                            e.preventDefault();
                            toggleSelection();
                        }
                    });

                    imageList.append(img);
                });

                // Restore selected state
                imageList.find('.image-item').each(function() {
                    const url = $(this).data('url');
                    if (selectedImages.has(url)) {
                        $(this).addClass('selected')
                               .find('.image-overlay').show();
                        $(this).attr('aria-selected', true);
                    }
                });
            }

            function updatePagination(data) {
                currentPage = data.current_page;
                dialog.find('.current-page').text(currentPage);
                dialog.find('.total-pages').text(data.last_page);

                const prevButton = dialog.find('.prev-page');
                const nextButton = dialog.find('.next-page');

                prevButton.prop('disabled', !data.prev_page_url);
                nextButton.prop('disabled', !data.next_page_url);

                prevButton.attr('aria-disabled', !data.prev_page_url);
                nextButton.attr('aria-disabled', !data.next_page_url);
            }

            function updateSelectedCount() {
                const count = selectedImages.size;
                dialog.find('.selected-count').text(count + ' image' + (count !== 1 ? 's' : '') + ' selected');
                dialog.find('.insert-images').prop('disabled', count === 0);
            }

            // Event handlers
            dialog.find('#image-upload-input').on('change', async function(e) {
                const files = Array.from(e.target.files);
                if (!files.length) return;

                const progressContainer = dialog.find('.upload-progress-container');
                const overallProgress = dialog.find('.overall-progress .progress-bar');
                const overallStatus = dialog.find('.overall-status');
                const individualProgress = dialog.find('.individual-progress');

                progressContainer.show();
                individualProgress.empty();

                let completedFiles = 0;
                const totalFiles = files.length;

                const updateOverallProgress = () => {
                    const percentage = (completedFiles / totalFiles) * 100;
                    overallProgress.css('width', percentage + '%').attr('aria-valuenow', percentage);
                    overallStatus.text(completedFiles + '/' + totalFiles + ' files');
                };

                const createProgressItem = (file) => {
                    const item = $('<div class="mb-2">' +
                        '<div class="d-flex justify-content-between mb-1">' +
                        '<small class="text-truncate" style="max-width: 200px;" title="' + file.name + '">' + file.name + '</small>' +
                        '<small class="status">Waiting...</small>' +
                        '</div>' +
                        '<div class="progress" style="height: 4px;">' +
                        '<div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>' +
                        '</div>' +
                        '</div>');
                    individualProgress.append(item);
                    return item;
                };

                const uploadFile = async (file) => {
                    const formData = new FormData();
                    formData.append('file', file);

                    const progressItem = createProgressItem(file);
                    const progressBar = progressItem.find('.progress-bar');
                    const statusText = progressItem.find('.status');

                    try {
                        await new Promise((resolve, reject) => {
                            $.ajax({
                                url: '$uploadUrl',
                                method: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                xhr: function() {
                                    const xhr = new window.XMLHttpRequest();
                                    xhr.upload.addEventListener('progress', function(e) {
                                        if (e.lengthComputable) {
                                            const percentComplete = (e.loaded / e.total) * 100;
                                            progressBar.css('width', percentComplete + '%')
                                                     .attr('aria-valuenow', percentComplete);
                                            statusText.text(Math.round(percentComplete) + '%');
                                        }
                                    }, false);
                                    return xhr;
                                },
                                success: function(response) {
                                    statusText.html('<span class="text-success">Completed</span>');
                                    progressBar.addClass('bg-success');
                                    resolve();
                                },
                                error: function(xhr, status, error) {
                                    const errorMessage = xhr.responseJSON?.message || error || 'Unknown error';
                                    statusText.html('<span class="text-danger">Failed</span>');
                                    progressBar.addClass('bg-danger');
                                    progressItem.append('<small class="text-danger d-block">' + errorMessage + '</small>');
                                    reject(new Error(errorMessage));
                                }
                            });
                        });
                    } catch (error) {
                        console.error('Error uploading file:', file.name, error);
                    } finally {
                        completedFiles++;
                        updateOverallProgress();
                    }
                };

                // Process files with concurrency limit
                const concurrencyLimit = 3;
                const chunks = [];
                for (let i = 0; i < files.length; i += concurrencyLimit) {
                    chunks.push(files.slice(i, i + concurrencyLimit));
                }

                for (const chunk of chunks) {
                    await Promise.all(chunk.map(file => uploadFile(file)));
                }

                // All uploads completed
                setTimeout(() => {
                    loadImages(1, dialog.find('.search-input').val().trim());
                    setTimeout(() => {
                        progressContainer.hide();
                    }, 1500);
                }, 500);

                // Clear the input to allow uploading the same files again
                $(this).val('');
            });

            dialog.find('.search-input').on('input', function() {
                var searchValue = $(this).val().trim();

                if (searchTimer) {
                    clearTimeout(searchTimer);
                }

                searchTimer = setTimeout(function() {
                    loadImages(1, searchValue);
                }, SEARCH_DELAY);
            });

            dialog.find('.prev-page').click(function() {
                if (!$(this).prop('disabled') && currentPage > 1) {
                    loadImages(currentPage - 1, dialog.find('.search-input').val().trim());
                }
            });

            dialog.find('.next-page').click(function() {
                if (!$(this).prop('disabled')) {
                    loadImages(currentPage + 1, dialog.find('.search-input').val().trim());
                }
            });

            dialog.find('.insert-images').click(function() {
                var markdown = Array.from(selectedImages)
                    .map(url => {
                        const item = dialog.find('.image-item').filter(function() {
                            return $(this).data('url') === url;
                        });
                        const fullPath = item.data('full_path');
                        return '![' + fullPath + '](' + url + ')';
                    })
                    .join('\\n') + '\\n';

                if (markdown) {
                    cm.replaceSelection(markdown);
                }

                dialog.modal('hide');
            });

            dialog.on('hidden.bs.modal', function() {
                selectedImages.clear();
                dialog.remove();
            });

            dialog.on('shown.bs.modal', function() {
                dialog.find('.search-input').focus();
            });

            // Keyboard navigation
            dialog.on('keydown', function(e) {
                if (e.keyCode === KEY_CODES.ESCAPE) {
                    dialog.modal('hide');
                }
            });

            // Initialize
            $('body').append(dialog);
            dialog.modal({
                backdrop: 'static',
                keyboard: true
            });
            loadImages(1);
        };
    });
JS;
    }

    public static function options(): array
    {
        return [
            'imageUpload'  => false,
            'toolbarIcons' => [
                'undo', 'redo', '|',
                'bold', 'del', 'italic', 'quote', 'ucwords', 'uppercase', 'lowercase', '|',
                'h1', 'h2', 'h3', 'h4', 'h5', 'h6', '|',
                'list-ul', 'list-ol', 'hr', '|',
                'link', 'reference-link', 'image-library', 'code', 'preformatted-text', 'code-block', 'table', 'datetime', 'emoji', 'html-entities', 'pagebreak', '|',
                'goto-line', 'watch', 'preview', 'fullscreen', 'clear', 'search', '|', 'info',
            ],
            'toolbarIconsClass' => [
                'image-library' => 'fa-image',
            ],
            'toolbarIconTexts' => [
                'image-library' => 'Image Library',
            ],
        ];
    }
}
