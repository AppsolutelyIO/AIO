import { build, type InlineConfig } from 'vite';
import { resolve, basename } from 'path';
import { cpSync, copyFileSync, mkdirSync, readdirSync } from 'fs';
import { globSync } from 'glob';

const isProduction = process.env.NODE_ENV === 'production';
const outDir = isProduction ? 'resources/dist' : 'resources/pre-dist';

const dir = import.meta.dirname;

interface CssEntry {
    input: string;
    output: string;
}

// Shared SCSS options
const cssConfig: InlineConfig['css'] = {
    preprocessorOptions: {
        scss: {
            silenceDeprecations: ['import', 'global-builtin', 'if-function'],
        },
    },
};

// === JS/TS entries (build as IIFE) ===
const jsEntries: Record<string, string> = {
    'aio/js/app': resolve(dir, 'resources/assets/aio/js/app.ts'),
};

for (const f of globSync('resources/assets/aio/extra/*.ts')) {
    const name = basename(f, '.ts');
    if (name === 'markdown') continue; // CSS-only entry, handled below
    jsEntries[`aio/extra/${name}`] = resolve(dir, f);
}

// === CSS entries (SCSS → CSS) ===
const cssEntries: CssEntry[] = [
    { input: 'resources/assets/adminlte/scss/AdminLTE.scss', output: 'adminlte/adminlte.css' },
    { input: 'resources/assets/aio/sass/app.scss', output: 'aio/css/app.css' },
    { input: 'resources/assets/aio/extra/upload.scss', output: 'aio/extra/upload.css' },
    { input: 'resources/assets/aio/extra/markdown.scss', output: 'aio/extra/markdown.css' },
];

async function buildAll(): Promise<void> {
    // 1. Build all JS/TS entries as IIFE (no CSS extraction)
    for (const [name, entry] of Object.entries(jsEntries)) {
        console.log(`[JS]  ${name}`);
        await build({
            configFile: false,
            css: cssConfig,
            build: {
                outDir,
                emptyOutDir: false,
                sourcemap: true,
                cssCodeSplit: false,
                rollupOptions: {
                    input: { [name]: entry },
                    output: {
                        format: 'iife',
                        entryFileNames: '[name].js',
                        assetFileNames: '[name][extname]',
                    },
                },
            },
        });
    }

    // 2. Build CSS entries
    for (const { input, output } of cssEntries) {
        console.log(`[CSS] ${output}`);
        await build({
            configFile: false,
            css: cssConfig,
            plugins: [
                {
                    name: 'css-rename',
                    generateBundle(_options, bundle) {
                        for (const [key, asset] of Object.entries(bundle)) {
                            if (asset.type === 'asset' && key.endsWith('.css')) {
                                asset.fileName = output;
                            }
                            if (asset.type === 'chunk') {
                                delete bundle[key];
                            }
                        }
                    },
                },
            ],
            build: {
                outDir,
                emptyOutDir: false,
                sourcemap: true,
                rollupOptions: {
                    input: { [output.replace('.css', '')]: resolve(dir, input) },
                    output: {
                        format: 'es',
                        entryFileNames: '[name].js',
                        assetFileNames: '[name][extname]',
                    },
                },
            },
        });
    }

    // 3. Copy static assets
    console.log('[COPY] static assets');
    cpSync('resources/assets/images', `${outDir}/images`, { recursive: true });
    cpSync('resources/assets/fonts', `${outDir}/fonts`, { recursive: true });

    // Plugins sourced from npm (skip when copying vendored assets)
    const npmPlugins = new Set([
        'autocomplete', 'bootstrap-colorpicker', 'bootstrap-datetimepicker',
        'bootstrap-duallistbox', 'bootstrap-validator', 'charts',
        'extensions', 'fontawesome-iconpicker', 'input-mask', 'ionslider',
        'jquery.initialize', 'jstree-theme', 'moment',
        'moment-timezone', 'nestable', 'select', 'sortable', 'tinymce',
        'vditor',
    ]);

    // Copy vendored plugins (those not sourced from npm)
    const pluginsSrc = 'resources/assets/aio/plugins';
    const pluginsDest = `${outDir}/aio/plugins`;
    mkdirSync(pluginsDest, { recursive: true });
    for (const entry of readdirSync(pluginsSrc)) {
        if (npmPlugins.has(entry)) continue;
        cpSync(`${pluginsSrc}/${entry}`, `${pluginsDest}/${entry}`, { recursive: true });
    }

    // Copy npm-sourced plugins
    console.log('[COPY] npm plugins');
    const p = pluginsDest;
    const copy = (src: string, dest: string) => {
        mkdirSync(resolve(dest, '..'), { recursive: true });
        cpSync(src, dest, { recursive: true });
    };

    // select2
    copy('node_modules/select2/dist/js/select2.full.min.js', `${p}/select/select2.full.min.js`);
    copy('node_modules/select2/dist/css/select2.min.css', `${p}/select/select2.min.css`);
    copy('node_modules/select2/dist/js/i18n', `${p}/select/i18n`);

    // apexcharts
    copy('node_modules/apexcharts/dist/apexcharts.min.js', `${p}/charts/apexcharts.min.js`);

    // sortablejs
    copy('node_modules/sortablejs/Sortable.min.js', `${p}/sortable/Sortable.min.js`);

    // moment
    copy('node_modules/moment/min/moment-with-locales.min.js', `${p}/moment/moment-with-locales.min.js`);

    // moment-timezone
    copy('node_modules/moment-timezone/builds/moment-timezone-with-data.min.js', `${p}/moment-timezone/moment-timezone-with-data.min.js`);

    // bootstrap-validator
    copy('node_modules/bootstrap-validator/dist/validator.min.js', `${p}/bootstrap-validator/validator.min.js`);

    // jquery-nestable (no minified JS or CSS in npm; keep vendored CSS)
    copy('node_modules/jquery-nestable/jquery.nestable.js', `${p}/nestable/jquery.nestable.min.js`);
    cpSync(`${pluginsSrc}/nestable/nestable.css`, `${p}/nestable/nestable.css`);

    // ion-rangeslider (skin CSS and sprites not in npm; keep vendored)
    copy('node_modules/ion-rangeslider/js/ion.rangeSlider.min.js', `${p}/ionslider/ion.rangeSlider.min.js`);
    copy('node_modules/ion-rangeslider/css/ion.rangeSlider.css', `${p}/ionslider/ion.rangeSlider.css`);
    cpSync(`${pluginsSrc}/ionslider/ion.rangeSlider.skinNice.css`, `${p}/ionslider/ion.rangeSlider.skinNice.css`);
    cpSync(`${pluginsSrc}/ionslider/ion.rangeSlider.skinFlat.css`, `${p}/ionslider/ion.rangeSlider.skinFlat.css`);
    cpSync(`${pluginsSrc}/ionslider/img`, `${p}/ionslider/img`, { recursive: true });

    // jquery-pjax: uses vendored custom version (includes SeaJS + pjax:loaded event)
    // NOT sourced from npm — the npm version lacks the sequential script loader

    // jquery.initialize
    copy('node_modules/jquery.initialize/jquery.initialize.min.js', `${p}/jquery.initialize/jquery.initialize.min.js`);

    // jstree (proton theme not in npm; keep vendored)
    copy('node_modules/jstree/dist/jstree.min.js', `${p}/jstree-theme/jstree.min.js`);
    cpSync(`${pluginsSrc}/jstree-theme/themes`, `${p}/jstree-theme/themes`, { recursive: true });

    // bootstrap4-datetimepicker (pc-bootstrap4-datetimepicker)
    copy('node_modules/pc-bootstrap4-datetimepicker/build/js/bootstrap-datetimepicker.min.js', `${p}/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js`);
    copy('node_modules/pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.min.css', `${p}/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css`);

    // devbridge-autocomplete
    copy('node_modules/devbridge-autocomplete/dist/jquery.autocomplete.min.js', `${p}/autocomplete/jquery.autocomplete.min.js`);

    // jquery.inputmask (phone-codes kept as vendored assets)
    copy('node_modules/jquery.inputmask/dist/jquery.inputmask.bundle.js', `${p}/input-mask/jquery.inputmask.bundle.min.js`);
    cpSync(`${pluginsSrc}/input-mask/phone-codes`, `${p}/input-mask/phone-codes`, { recursive: true });

    // bootstrap-duallistbox
    copy('node_modules/bootstrap-duallistbox/dist/jquery.bootstrap-duallistbox.min.js', `${p}/bootstrap-duallistbox/dist/jquery.bootstrap-duallistbox.min.js`);
    copy('node_modules/bootstrap-duallistbox/dist/bootstrap-duallistbox.min.css', `${p}/bootstrap-duallistbox/dist/bootstrap-duallistbox.min.css`);

    // tinymce (langs from tinymce-i18n)
    copy('node_modules/tinymce/tinymce.min.js', `${p}/tinymce/tinymce.min.js`);
    copy('node_modules/tinymce/tinymce.d.ts', `${p}/tinymce/tinymce.d.ts`);
    copy('node_modules/tinymce/themes', `${p}/tinymce/themes`);
    copy('node_modules/tinymce/skins', `${p}/tinymce/skins`);
    copy('node_modules/tinymce/plugins', `${p}/tinymce/plugins`);
    copy('node_modules/tinymce/icons', `${p}/tinymce/icons`);
    copy('node_modules/tinymce-i18n/langs5', `${p}/tinymce/langs`);

    // toastr (extensions dir only needs toastr; sweetalert2 is bundled via TS)
    copy('node_modules/toastr/build/toastr.min.js', `${p}/extensions/toastr.min.js`);
    copy('node_modules/toastr/build/toastr.css', `${p}/extensions/toastr.css`);

    // bootstrap-colorpicker
    copy('node_modules/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js', `${p}/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js`);
    copy('node_modules/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css', `${p}/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css`);

    // fontawesome-iconpicker
    copy('node_modules/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.js', `${p}/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.js`);
    copy('node_modules/fontawesome-iconpicker/dist/css/fontawesome-iconpicker.min.css', `${p}/fontawesome-iconpicker/dist/css/fontawesome-iconpicker.min.css`);

    // vditor
    copy('node_modules/vditor/dist', `${p}/vditor/dist`);

    mkdirSync(`${outDir}/aio/css`, { recursive: true });
    copyFileSync('resources/assets/aio/sass/nunito.css', `${outDir}/aio/css/nunito.css`);

    // 4. Copy AdminLTE JS from npm package (pre-built)
    console.log('[COPY] AdminLTE JS (from npm)');
    mkdirSync(`${outDir}/adminlte`, { recursive: true });
    const adminlteSrc = isProduction ? 'adminlte.min.js' : 'adminlte.js';
    copyFileSync(`node_modules/admin-lte/dist/js/${adminlteSrc}`, `${outDir}/adminlte/adminlte.js`);
    copyFileSync(`node_modules/admin-lte/dist/js/${adminlteSrc}.map`, `${outDir}/adminlte/adminlte.js.map`);

    console.log('Done.');
}

buildAll().catch((err: unknown) => {
    console.error(err);
    process.exit(1);
});
