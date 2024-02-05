<template>
	<view class="app-content">
		<view class="app-cate-item" v-for="(item,key) in service" :key="key">
			<view class="app-item-title">{{item.label}}</view>
			<u-grid :border="false" col="4">
				<block v-for="(listItem,listIndex) in item.children" :key="listIndex">
					<u-grid-item @click="assistantInfo(listItem)" >
						<u-icon :customStyle="{paddingTop:20+'rpx'}" :name="listItem.icon" size="90rpx"></u-icon>
						<text class="grid-text" v-if="listItem.id == assistant_id" style="font-weight:bolder;color: #1acc89;">{{listItem.label}}</text>
						<text class="grid-text" v-if="listItem.id != assistant_id">{{listItem.label}}</text>
					</u-grid-item>
				</block>
			</u-grid>
		</view>
	</view>
</template>

<script>

	export default {
		components: {
			
		},
		data() {
			return {
				service: [],
				
				assistant_id :0,
			}
		},
		onLoad(options) {
			uni.setNavigationBarTitle({
				title:uni.getStorageSync('channel_info').name
			})
			if(options.assistant_id != undefined){
				this.assistant_id = options.assistant_id
			}
			
			this.getApps();
		},
		methods: {
			
			async getApps() {
				var res = await this.$http.requestApi('GET', '/cate/cateAssistant');
				this.service = res.data;
			},
			assistantInfo(e){
				
				if (e.apppath == '') {
					uni.reLaunch({
						url:"/pages/create/index?assistant_id="+e.id
					})
				}else{
					uni.navigateToMiniProgram({
						appId: e.appid,
						path: e.apppath,
						fail() {}
					})
				}
			},
			
		}
	}
</script>

<style lang="scss">
	

	.app-content {
		width: 92%;
		margin: 20rpx auto;

		.app-cate-item {
			background-color: $uni-bg-color;
			border-radius: 14rpx;
			padding: 10rpx;
			margin-bottom: 20rpx;

			.app-item-title {
				padding: 20rpx;
				font-weight: bolder;
			}
		}
	}

	.grid-text {
		font-size: 12px;
		color: #909399;
		padding: 10rpx 0 20rpx 0rpx;
		/* #ifndef APP-PLUS */
		box-sizing: border-box;
		/* #endif */
	}
</style>