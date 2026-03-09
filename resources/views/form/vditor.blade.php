<div class="{{$viewClass['form-group']}}">

    <label class="{{$viewClass['label']}} control-label">{!! $label !!}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <div id="{{ $id }}" class="{{ $class }}" {!! $attributes !!}></div>
        <input type="hidden" name="{{ $name }}" id="{{ $id }}_val" value="{{ $value ?? '' }}">

        @include('admin::form.help-block')

    </div>
</div>

<script require="@vditor" init="{!! $selector !!}">
    var _vditorValue = @json($value ?? '');
    var _vditorOptions = {!! json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};

    var _renderMarkmapBlock = function (element) {
        var blocks = element.querySelectorAll('.language-markmap, code.language-markmap');
        if (!blocks.length) return;

        var loadedFeatures = {};
        var doRender = function () {
            var mm = window.markmap;
            if (!mm || !mm.Markmap) return;
            var transformer = new mm.Transformer();

            blocks.forEach(function (block) {
                if (!block || block.dataset.rendered) return;
                block.dataset.rendered = '1';

                var text = (block.textContent || '').trim();
                if (!text) {
                    delete block.dataset.rendered;
                    return;
                }

                try {
                    var result = transformer.transform(text);
                    var features = Object.keys(result.features || {}).filter(function (feature) {
                        return !loadedFeatures[feature];
                    });

                    features.forEach(function (feature) {
                        loadedFeatures[feature] = true;
                    });

                    if (typeof transformer.getAssets === 'function') {
                        var assets = transformer.getAssets(features);
                        if (assets.styles && mm.loadCSS) {
                            mm.loadCSS(assets.styles);
                        }
                        if (assets.scripts && mm.loadJS) {
                            mm.loadJS(assets.scripts);
                        }
                    }

                    var host = block.tagName === 'CODE' ? block.parentElement : block;
                    host.innerHTML = '<svg style="width:100%;min-height:320px"></svg>';

                    var svg = host.firstChild;
                    var root = result.root || result;
                    var options = null;
                    if (typeof mm.deriveOptions === 'function') {
                        options = mm.deriveOptions((result.frontmatter || {}).markmap);
                    }

                    var map = mm.Markmap.create(svg, options || null);
                    if (typeof map.setData === 'function') {
                        if (options) {
                            map.setData(root, options);
                        } else {
                            map.setData(root);
                        }
                    }
                    if (typeof map.fit === 'function') {
                        map.fit();
                    }
                } catch (e) {
                    delete block.dataset.rendered;
                }
            });
        };

        if (window.markmap && window.markmap.Markmap) {
            doRender();
        } else {
            var s = document.createElement('script');
            s.src = '{{ $cdn }}/dist/js/markmap/markmap.min.js';
            s.onload = doRender;
            document.head.appendChild(s);
        }
    };

    _vditorOptions.customRenders = [
        {
            language: 'mermaid',
            render: function (element) {
                var codes = element.querySelectorAll('code.language-mermaid');
                if (!codes.length) return;
                var doRender = function () {
                    mermaid.initialize({ startOnLoad: false, theme: 'default', securityLevel: 'loose' });
                    codes.forEach(function (code, i) {
                        if (code.dataset.rendered) return;
                        code.dataset.rendered = '1';
                        var pre = code.parentElement;
                        var id = 'mermaid-{{ str_replace("-", "_", $id) }}-' + Date.now() + i;
                        var text = code.textContent.trim();
                        mermaid.render(id, text).then(function (r) {
                            pre.innerHTML = r.svg;
                        }).catch(function () {
                            delete code.dataset.rendered;
                        });
                    });
                };
                if (typeof mermaid !== 'undefined') {
                    doRender();
                } else {
                    var s = document.createElement('script');
                    s.src = '{{ $cdn }}/dist/js/mermaid/mermaid.min.js';
                    s.onload = doRender;
                    document.head.appendChild(s);
                }
            }
        },
        {
            language: 'echarts',
            render: function (element) {
                var codes = element.querySelectorAll('code.language-echarts');
                if (!codes.length) return;
                var doRender = function () {
                    codes.forEach(function (code) {
                        if (code.dataset.rendered) return;
                        code.dataset.rendered = '1';
                        var pre = code.parentElement;
                        try {
                            var option = JSON.parse(code.textContent.trim());
                        } catch (e) {
                            delete code.dataset.rendered;
                            return;
                        }
                        pre.style.height = pre.style.height || '360px';
                        pre.innerHTML = '';
                        echarts.init(pre).setOption(option);
                    });
                };
                if (typeof echarts !== 'undefined') {
                    doRender();
                } else {
                    var s = document.createElement('script');
                    s.src = '{{ $cdn }}/dist/js/echarts/echarts.min.js';
                    s.onload = doRender;
                    document.head.appendChild(s);
                }
            }
        },
        {
            language: 'markmap',
            render: function (element) {
                _renderMarkmapBlock(element);
            }
        },
        {
            language: 'flowchart',
            render: function (element) {
                var codes = element.querySelectorAll('code.language-flowchart');
                if (!codes.length) return;
                var doRender = function () {
                    codes.forEach(function (code) {
                        if (code.dataset.rendered) return;
                        code.dataset.rendered = '1';
                        var pre = code.parentElement;
                        var text = code.textContent.trim();
                        try {
                            pre.innerHTML = '';
                            flowchart.parse(text).drawSVG(pre);
                        } catch (e) {
                            delete code.dataset.rendered;
                        }
                    });
                };
                if (typeof flowchart !== 'undefined') {
                    doRender();
                } else {
                    var s = document.createElement('script');
                    s.src = '{{ $cdn }}/dist/js/flowchart.js/flowchart.min.js';
                    s.onload = doRender;
                    document.head.appendChild(s);
                }
            }
        },
        {
            language: 'graphviz',
            render: function (element) {
                var codes = element.querySelectorAll('code.language-graphviz');
                if (!codes.length) return;
                var cdn = '{{ $cdn }}';
                var doRender = function () {
                    codes.forEach(function (code) {
                        if (code.dataset.rendered) return;
                        code.dataset.rendered = '1';
                        var pre = code.parentElement;
                        var text = code.textContent.trim();
                        var viz = new Viz({ workerURL: cdn + '/dist/js/graphviz/full.render.js' });
                        viz.renderSVGElement(text).then(function (svg) {
                            svg.style.width = '100%';
                            pre.innerHTML = '';
                            pre.appendChild(svg);
                        }).catch(function () {
                            delete code.dataset.rendered;
                        });
                    });
                };
                if (typeof Viz !== 'undefined') {
                    doRender();
                } else {
                    var s = document.createElement('script');
                    s.src = cdn + '/dist/js/graphviz/viz.js';
                    s.onload = doRender;
                    document.head.appendChild(s);
                }
            }
        },
        {
            language: 'plantuml',
            render: function (element) {
                var codes = element.querySelectorAll('code.language-plantuml');
                if (!codes.length) return;
                var doRender = function () {
                    codes.forEach(function (code) {
                        if (code.dataset.rendered) return;
                        code.dataset.rendered = '1';
                        var pre = code.parentElement;
                        var text = code.textContent.trim();
                        var encoded = plantumlEncoder.encode(text);
                        var obj = document.createElement('object');
                        obj.type = 'image/svg+xml';
                        obj.data = 'https://www.plantuml.com/plantuml/svg/~1' + encoded;
                        obj.style.width = '100%';
                        pre.innerHTML = '';
                        pre.appendChild(obj);
                    });
                };
                if (typeof plantumlEncoder !== 'undefined') {
                    doRender();
                } else {
                    var s = document.createElement('script');
                    s.src = '{{ $cdn }}/dist/js/plantuml/plantuml-encoder.min.js';
                    s.onload = doRender;
                    document.head.appendChild(s);
                }
            }
        }
    ];

    // 自动跟随系统深色模式
    var _applyTheme = function (dark) {
        _vditorOptions.theme = dark ? 'dark' : 'classic';
        _vditorOptions.preview.theme.current = dark ? 'dark' : 'light';
        _vditorOptions.preview.hljs.style = dark ? 'monokai' : 'github';
    };
    var _mq = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)');
    _applyTheme(_mq && _mq.matches);

    if (typeof _vditorOptions.customWysiwygToolbar !== 'function') {
        _vditorOptions.customWysiwygToolbar = function () {};
    }
    _vditorOptions.input = function (val) {
        document.getElementById('{{ $id }}_val').value = val;
    };
    _vditorOptions.after = function () {
        if (_vditorValue) {
            _vditor_{{ str_replace('-', '_', $id) }}.setValue(_vditorValue);
        }
        document.getElementById('{{ $id }}_val').value = _vditorValue;
        // 监听系统主题变化，实时切换编辑器主题
        if (_mq && _mq.addEventListener) {
            _mq.addEventListener('change', function (e) {
                _vditor_{{ str_replace('-', '_', $id) }}.setTheme(
                    e.matches ? 'dark' : 'classic',
                    e.matches ? 'dark' : 'light',
                    e.matches ? 'monokai' : 'github'
                );
            });
        }
    };
    _vditorOptions.esc = function () {
        if (document.fullscreenElement) {
            document.exitFullscreen && document.exitFullscreen();
        } else {
            document.activeElement && document.activeElement.blur();
        }
    };
    _vditorOptions.ctrlEnter = function () {
        var form = document.getElementById('{{ $id }}_val').closest('form');
        if (form) form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    };
    _vditorOptions.upload.format = function (files, responseText) {
        var res = JSON.parse(responseText);
        // For PDF files, ensure they are inserted as links [name](url) rather than images
        if (res && res.data && res.data.succMap) {
            var converted = {};
            files.forEach(function (file) {
                var url = res.data.succMap[file.name];
                if (!url) return;
                if (file.type === 'application/pdf') {
                    // Prefix with 'file:' so Vditor treats it as a downloadable link
                    converted['file:' + file.name] = url;
                } else {
                    converted[file.name] = url;
                }
            });
            res.data.succMap = converted;
        }
        return JSON.stringify(res);
    };

    var _vditor_{{ str_replace('-', '_', $id) }} = new Vditor('{{ $id }}', _vditorOptions);
</script>
