<template>
	<view>
	
	
	<view class="container">
		
		
		
		
		<view class="form">
			
			<u-transition :show="true" mode="slide-left">
				<view class="title">欢迎使用 {{title}}</view>
			</u-transition>
			<u-transition :show="true" mode="slide-left">
				<view class="desc">智能文案生成，创新无限可能</view>
			</u-transition>
			<image class="img" :src="cdnUrl+'/login.jpg'" mode=""></image>
			<view class="btn-group">
				<view class="btn">
					<u-button shape="circle" :loading="loginLoading" color="linear-gradient(-90deg, #26B3A0, #96E8BA)"
						text="微信用户一键登录" @click="wxMiniLogin">
					</u-button>
				</view>
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
				
				code: "",
				cdnUrl:this.$config.cdnUrl,
				title:uni.getStorageSync('channel_info').name
				
			}
		},
		onShow() {
			uni.setNavigationBarTitle({
				title:uni.getStorageSync('channel_info').name
			})
		},
		methods: {
			wxMiniLogin() {
				uni.login({
					provider: "weixin",
					success: (res) => {
						var data = {};
						data.code = res.code;
						this.requestLogin(data);
					},
				});
			},
			requestLogin(postData) {
				
				this.loginLoading = true;
				this.$http.requestApi('POST', '/user/wxMiniLogin', postData).then(res => {
					const resData = res.data;
					const resCode = res.code;
					this.loginLoading = false;
					if (resCode == 0) {
						uni.setStorageSync('token', resData.accessToken);
						uni.setStorageSync('userInfo', resData.userInfo);
						
						uni.showToast({
							title: '登录成功！',
							icon: 'none'
						});
						
						let pages = getCurrentPages(); //页面对象
						let prevpage = pages[pages.length - 2]; //上一个页面对象
						let fullPath = prevpage.$page.fullPath;
						if (fullPath) {
							uni.reLaunch({
								url: fullPath,
							})
						} else {
							uni.navigateBack({
								delta: 1
							})
						}
					} else {
						uni.showToast({
							title: '登录失败，请稍后重试！',
							icon: 'none'
						});
					}
				})
			}
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
			margin-top: 35rpx;
			font-size: 28rpx;
			color: #666;
			margin-bottom: 35rpx;
			
		}

		.btn-group {
			width: 80%;
			
			.btn {
				margin: 60rpx 0rpx;

				.u-button {
					height: 100rpx;
				}
			}
		}
		
		.img{
			mix-blend-mode: darken;
		}
	}
</style>