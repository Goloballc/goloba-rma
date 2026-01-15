/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './src/Resources/views/**/*.blade.php',
        './src/Resources/assets/**/*.js',
    ],
    theme: {
        extend: {
            // Extensiones custom cuando sea necesario
            colors: {
                // 'goloba-primary': '#your-color',
            },
        },
    },
    plugins: [],
}
