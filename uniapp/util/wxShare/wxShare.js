export default {
    data() {
        return {
            share: {
                // 转发的标题 （默认标题）
                title: '分享标题',
                // 默认是当前页面，必须是以‘/’开头的完整路径
                path: '',
                //自定义图片路径，可以是本地文件路径、代码包文件路径或者网络图片路径，
                //支持PNG及JPG，不传入 imageUrl 则使用默认截图。显示图片长宽比是 5:4
                imageUrl: ''
            }
        }
    },
    onShareAppMessage(res) {
		this.$http.requestApi('POST', '/user/addNums').then(res => {
			
		});
    	return {
    		title: '推荐你一个写作神器，写啥都方便！',
    		path: '/pages/index/index',
			imageUrl: this.$config.cdnUrl+'/share1.png'
    	}
    },
    onShareTimeline(res) {
		this.$http.requestApi('POST', '/user/addNums').then(res => {
			
		});
    	return {
    		title: '推荐你一个写作神器，写啥都方便！',
    		path: '/pages/index/index',
    		imageUrl: this.$config.cdnUrl+'/share1.png'
    	}
    },
}