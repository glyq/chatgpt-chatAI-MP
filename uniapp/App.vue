<script>
	export default {
		onLaunch: function() {
			this.$http.requestApi('POST', '/index/getInfo',{showLoading:0}).then(res => {
				uni.setStorageSync('channel_info',res.data);
			});
			
		},
		onShow: function() {
			
		},
		onHide: function() {
			
		},
		
			
	}
</script>

<style lang="scss">
	@import "uview-ui/index.scss";
	page {
		font-size: 28rpx;
		background: url('static/images/bj.png') no-repeat;
		background-size: 100% 100%;
		left: 0;
		width: 100%;
		height: 100%;
		top:0
	}
</style>
