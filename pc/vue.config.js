module.exports = {
    devServer: {
        proxy: {
            '/api': {
                target: 'https://xxx.com',
                changeOrigin: true,
                pathRewrite: {
                    '^/api': ''
                },
                secure: false


            }
        }
    }
}
