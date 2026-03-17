/**
 * TDD Tests for prepareHtmlForEditor utilities
 * Tests for: attachDataSrcToSrc, makeLazyImagesVisible, prepareLazyContentForEditor,
 *            attachDataSrcToSrcInDocument, makeLazyImagesVisibleInDocument
 */

import {
    attachDataSrcToSrc,
    attachDataSrcToSrcInDocument,
    makeLazyImagesVisible,
    makeLazyImagesVisibleInDocument,
    prepareLazyContentForEditor,
} from '@resources/page-builder/assets/ts/utils/prepareHtmlForEditor';
import { describe, expect, it } from 'vitest';

describe('attachDataSrcToSrc', () => {
    it('should return empty string unchanged', () => {
        expect(attachDataSrcToSrc('')).toBe('');
    });

    it('should return whitespace-only string unchanged', () => {
        expect(attachDataSrcToSrc('   ')).toBe('   ');
    });

    it('should copy data-src to src on img with no src', () => {
        const html = '<img data-src="image.jpg">';
        const result = attachDataSrcToSrc(html);
        expect(result).toContain('src="image.jpg"');
    });

    it('should not overwrite existing non-empty src on img', () => {
        const html = '<img src="existing.jpg" data-src="new.jpg">';
        const result = attachDataSrcToSrc(html);
        const container = document.createElement('div');
        container.innerHTML = result;
        const img = container.querySelector('img') as HTMLImageElement;
        expect(img.getAttribute('src')).toBe('existing.jpg');
    });

    it('should copy data-src to src on video with no src', () => {
        const html = '<video data-src="video.mp4"></video>';
        const result = attachDataSrcToSrc(html);
        expect(result).toContain('src="video.mp4"');
    });

    it('should copy data-src to src on source element with no src', () => {
        const html = '<source data-src="media.mp4">';
        const result = attachDataSrcToSrc(html);
        expect(result).toContain('src="media.mp4"');
    });

    it('should handle img with empty src attribute', () => {
        const html = '<img src="" data-src="image.jpg">';
        const result = attachDataSrcToSrc(html);
        expect(result).toContain('src="image.jpg"');
    });

    it('should not modify elements without data-src', () => {
        const html = '<img src="normal.jpg"><p>Text</p>';
        const result = attachDataSrcToSrc(html);
        expect(result).toContain('src="normal.jpg"');
        expect(result).toContain('<p>Text</p>');
    });

    it('should handle multiple lazy images', () => {
        const html = '<img data-src="a.jpg"><img data-src="b.jpg"><img data-src="c.jpg">';
        const result = attachDataSrcToSrc(html);
        expect(result).toContain('src="a.jpg"');
        expect(result).toContain('src="b.jpg"');
        expect(result).toContain('src="c.jpg"');
    });

    it('should leave non-media elements unchanged', () => {
        const html = '<div data-src="something"><p>Text</p></div>';
        const result = attachDataSrcToSrc(html);
        // div with data-src should not get src attribute
        const container = document.createElement('div');
        container.innerHTML = result;
        const div = container.querySelector('div');
        expect(div?.getAttribute('src')).toBeNull();
    });
});

describe('makeLazyImagesVisible', () => {
    it('should return empty string unchanged', () => {
        expect(makeLazyImagesVisible('')).toBe('');
    });

    it('should return whitespace-only string unchanged', () => {
        expect(makeLazyImagesVisible('   ')).toBe('   ');
    });

    it('should add lazy-loaded class to img elements', () => {
        const html = '<img src="image.jpg">';
        const result = makeLazyImagesVisible(html);
        expect(result).toContain('lazy-loaded');
    });

    it('should set opacity to 1 on img elements', () => {
        const html = '<img src="image.jpg">';
        const result = makeLazyImagesVisible(html);
        expect(result).toContain('opacity: 1');
    });

    it('should process all img elements in the HTML', () => {
        const html = '<img src="a.jpg"><img src="b.jpg"><img src="c.jpg">';
        const container = document.createElement('div');
        container.innerHTML = makeLazyImagesVisible(html);
        const imgs = container.querySelectorAll('img');
        imgs.forEach((img) => {
            expect(img.classList.contains('lazy-loaded')).toBe(true);
            expect(img.style.opacity).toBe('1');
        });
    });

    it('should not affect non-img elements', () => {
        const html = '<div class="wrapper"><video src="v.mp4"></video></div>';
        const result = makeLazyImagesVisible(html);
        expect(result).toContain('class="wrapper"');
        const container = document.createElement('div');
        container.innerHTML = result;
        const video = container.querySelector('video');
        expect(video?.classList.contains('lazy-loaded')).toBe(false);
    });

    it('should leave HTML without images unchanged in structure', () => {
        const html = '<p>Hello <strong>World</strong></p>';
        const result = makeLazyImagesVisible(html);
        expect(result).toContain('<p>');
        expect(result).toContain('<strong>World</strong>');
    });
});

describe('prepareLazyContentForEditor', () => {
    it('should return empty string unchanged', () => {
        expect(prepareLazyContentForEditor('')).toBe('');
    });

    it('should return whitespace-only string unchanged', () => {
        expect(prepareLazyContentForEditor('   ')).toBe('   ');
    });

    it('should copy data-src to src AND add lazy-loaded class to img', () => {
        const html = '<img data-src="photo.jpg">';
        const result = prepareLazyContentForEditor(html);
        expect(result).toContain('src="photo.jpg"');
        expect(result).toContain('lazy-loaded');
        expect(result).toContain('opacity: 1');
    });

    it('should not overwrite existing non-empty src', () => {
        const html = '<img src="existing.jpg" data-src="new.jpg">';
        const result = prepareLazyContentForEditor(html);
        expect(result).toContain('src="existing.jpg"');
    });

    it('should process video data-src without adding lazy-loaded', () => {
        const html = '<video data-src="video.mp4"></video>';
        const result = prepareLazyContentForEditor(html);
        expect(result).toContain('src="video.mp4"');
        const container = document.createElement('div');
        container.innerHTML = result;
        const video = container.querySelector('video');
        expect(video?.classList.contains('lazy-loaded')).toBe(false);
    });

    it('should process source data-src without adding lazy-loaded', () => {
        const html = '<source data-src="media.mp4">';
        const result = prepareLazyContentForEditor(html);
        expect(result).toContain('src="media.mp4"');
    });

    it('should handle mixed content', () => {
        const html = `
            <div>
                <img data-src="img1.jpg">
                <video data-src="video.mp4"></video>
                <img src="normal.jpg">
                <p>Text content</p>
            </div>
        `;
        const result = prepareLazyContentForEditor(html);
        const container = document.createElement('div');
        container.innerHTML = result;

        const lazyImg = container.querySelector('img[data-src]') as HTMLImageElement;
        const normalImg = container.querySelector('img[src="normal.jpg"]') as HTMLImageElement;
        const video = container.querySelector('video') as HTMLVideoElement;

        expect(lazyImg.getAttribute('src')).toBe('img1.jpg');
        expect(lazyImg.classList.contains('lazy-loaded')).toBe(true);
        expect(normalImg.getAttribute('src')).toBe('normal.jpg');
        expect(video.getAttribute('src')).toBe('video.mp4');
    });
});

describe('attachDataSrcToSrcInDocument', () => {
    it('should copy data-src to src on img elements in document', () => {
        document.body.innerHTML = '<img data-src="image.jpg">';
        attachDataSrcToSrcInDocument(document);
        const img = document.querySelector('img') as HTMLImageElement;
        expect(img.getAttribute('src')).toBe('image.jpg');
    });

    it('should not overwrite existing non-empty src', () => {
        document.body.innerHTML = '<img src="existing.jpg" data-src="new.jpg">';
        attachDataSrcToSrcInDocument(document);
        const img = document.querySelector('img') as HTMLImageElement;
        expect(img.getAttribute('src')).toBe('existing.jpg');
    });

    it('should process video elements in document', () => {
        document.body.innerHTML = '<video data-src="video.mp4"></video>';
        attachDataSrcToSrcInDocument(document);
        const video = document.querySelector('video') as HTMLVideoElement;
        expect(video.getAttribute('src')).toBe('video.mp4');
    });

    it('should process source elements in document', () => {
        document.body.innerHTML = '<source data-src="audio.mp3">';
        attachDataSrcToSrcInDocument(document);
        const source = document.querySelector('source') as HTMLSourceElement;
        expect(source.getAttribute('src')).toBe('audio.mp3');
    });

    it('should process multiple elements in document', () => {
        document.body.innerHTML = `
            <img data-src="a.jpg">
            <img data-src="b.jpg">
            <video data-src="v.mp4"></video>
        `;
        attachDataSrcToSrcInDocument(document);
        const imgs = document.querySelectorAll('img');
        expect(imgs[0].getAttribute('src')).toBe('a.jpg');
        expect(imgs[1].getAttribute('src')).toBe('b.jpg');
        const video = document.querySelector('video') as HTMLVideoElement;
        expect(video.getAttribute('src')).toBe('v.mp4');
    });

    it('should not modify elements without data-src', () => {
        document.body.innerHTML = '<img src="normal.jpg"><p>Text</p>';
        attachDataSrcToSrcInDocument(document);
        const img = document.querySelector('img') as HTMLImageElement;
        expect(img.getAttribute('src')).toBe('normal.jpg');
    });
});

describe('makeLazyImagesVisibleInDocument', () => {
    it('should add lazy-loaded class to all img elements in document', () => {
        document.body.innerHTML = '<img src="a.jpg"><img src="b.jpg">';
        makeLazyImagesVisibleInDocument(document);
        const imgs = document.querySelectorAll('img');
        imgs.forEach((img) => {
            expect(img.classList.contains('lazy-loaded')).toBe(true);
        });
    });

    it('should set opacity to 1 on all img elements in document', () => {
        document.body.innerHTML = '<img src="a.jpg"><img src="b.jpg">';
        makeLazyImagesVisibleInDocument(document);
        const imgs = document.querySelectorAll('img');
        imgs.forEach((img) => {
            expect(img.style.opacity).toBe('1');
        });
    });

    it('should not affect non-img elements', () => {
        document.body.innerHTML = '<video src="v.mp4"></video><div class="wrapper"></div>';
        makeLazyImagesVisibleInDocument(document);
        const video = document.querySelector('video') as HTMLVideoElement;
        const div = document.querySelector('.wrapper') as HTMLElement;
        expect(video.classList.contains('lazy-loaded')).toBe(false);
        expect(div.classList.contains('lazy-loaded')).toBe(false);
    });

    it('should handle document with no images', () => {
        document.body.innerHTML = '<p>No images here</p>';
        // Should not throw
        expect(() => makeLazyImagesVisibleInDocument(document)).not.toThrow();
    });

    it('should make a single img visible', () => {
        document.body.innerHTML = '<img src="solo.jpg">';
        makeLazyImagesVisibleInDocument(document);
        const img = document.querySelector('img') as HTMLImageElement;
        expect(img.classList.contains('lazy-loaded')).toBe(true);
        expect(img.style.opacity).toBe('1');
    });
});
