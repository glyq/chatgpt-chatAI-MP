<template>
	<view>
		
		
		<view class="container">
			<view class="title"><u--text bold size="20" lineHeight="20" :text="title"></u--text></view>
			<u-line color="#1acc89"></u-line>
			<view class="content">
				<view class="list"><u--text size="15" lineHeight="20" :text="list"></u--text></view>
				
				
			</view>
		</view>
		
		
	</view>
</template>

<script>
	export default {
		components: {
			
		},
		data() {
			return {
				
				list : '',
				title:'',
				
			
			}
		},
		onLoad(options){
			uni.setNavigationBarTitle({
				title:uni.getStorageSync('project_title')
			})
			this.getNotice(options.id)
			
			
		},
		methods: {
			async getNotice(id){
				var data = await this.$http.requestApi('GET', '/user/getNotice',{id:id});
				this.list = data.data.info;
				this.title = data.data.title;
			},
		
		}
	}
</script>

<style lang="scss">
	.container{
		width: 100%;
	}
	.title{
		margin:20rpx;
	}
	.content{
		margin: 20rpx;
	}
	.info{
		margin: 20rpx;
	}
	.tag{
		float: left;
		margin-left: 10rpx;
		padding: 20rpx;
		width: 40%;
	}
	.finish{
		margin: 0 auto;
		width: 35%;
	}
</style>
