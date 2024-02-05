<template>
	<view>
	

	<view class="container">
		
		
		
		
		<view class="form">
			
			<u-transition :show="true" mode="slide-left">
				<view class="title">分享赢取无限次使用机会</view>
			</u-transition>
			 
			<u-transition :show="true" mode="slide-left">
				<view class="desc"></view>
			</u-transition>
			
			<view class="desc"><text>当前已有{{num}}位好友为你助力</text></view>
			
			<u-avatar-group
			           :urls="urls"
			           size="40"
			           gap="0.4"
									maxCount="15"
			></u-avatar-group>
			   
			   
			<view class="btn-group">
				<view class="btn">
					<u-button shape="circle"  color="linear-gradient(-90deg, #26B3A0, #96E8BA)"
						:text="title" open-type="share" :disabled="lock">
					</u-button>
				</view>
			</view>
			
			<view>
				<ad v-if="ad" ad-type="video" :unit-id="adVideo"></ad>
			</view>
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
				title:'',
				lock:false,
				num:0,
				urls: [],
				ad:uni.getStorageSync('ad'),
				adVideo: this.$config.adVideo
			}
		},
		onLoad(options){
			uni.setNavigationBarTitle({
				title:uni.getStorageSync('channel_info').name
			})
			this.id = options.id
			
		},
		onShow() {
			
			const token = uni.getStorageSync('token');
			if (token == '' || token == undefined) {
				uni.navigateTo({
					url: '/pages/my/login'
				});
			}
			this.getHelpInfo();
		},
		onShareAppMessage(res) {
			var userInfo = uni.getStorageSync('userInfo');
			return {
				title: '无限生成次数，来助力帮我赢取',
				path: '/pages/index/index?pid=' + userInfo.id,
				imageUrl: this.$config.cdnUrl+'/share1.png'
			}
		},
		onShareTimeline(res) {
			return {
				title: '无限生成次数，来助力帮我赢取',
				path: '/pages/index/index?pid=' + userInfo.id,
				imageUrl: this.$config.cdnUrl+'/share1.png'
			}
		},
		methods: {
			
			async getHelpInfo(){
				var data = await this.$http.requestApi('POST', '/user/getHelpInfo',{id:this.id});
				this.num = data.data.count;
				this.urls = data.data.urls;
				if(data.data.finish == 0){
					this.title = '邀请好友助力';
					this.lock = false;
				}else{
					this.title = '恭喜您已助力完成！';
					this.lock = true;
				}
			},
		}
	}
</script>

<style lang="scss">
	.form {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		margin-top: 10%;

		.title {
			font-size: 38rpx;
			font-weight: bolder;
			margin-top: 15rpx;
		}

		.desc {
			margin-top: 30rpx;
			margin-bottom: 30rpx;
			font-size: 28rpx;
			color: #666;
		}

		.btn-group {
			width: 80%;

			.btn {
				margin: 30rpx 0rpx;

				.u-button {
					height: 100rpx;
				}
			}
		}
	}
</style>