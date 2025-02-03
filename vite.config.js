import { defineConfig, loadEnv } from 'vite'

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        base: env.VITE_BASE_URL,
        build: {
            outDir: 'public/assets',
            copyPublicDir: false,
            rollupOptions: {
                input: {
                    'app': 'src/resources/css/app.css',
                    'home': 'src/resources/js/home.ts',
                    'upload': 'src/resources/js/upload.ts',
                    'post': '/src/resources/js/post.ts',
                    'tag': '/src/resources/js/tag.ts'
                },
                output: {
                    entryFileNames: 'js/[name].js',
                    chunkFileNames: 'js/[name]-[hash].js',
                    assetFileNames: ({name}) => {
                        if (/\.css$/.test(name)) {
                            return 'css/[name][extname]'
                        }
                        return 'assets/[name][extname]'
                    }
                }
            }
        }
    };
});