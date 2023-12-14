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
						text="一键登录PC端" @click="login">
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
				
				scene:'',
				cdnUrl:this.$config.cdnUrl,
				title:uni.getStorageSync('project_title')
				
			}
		},
		onShow() {
			
		},
		onLoad(options) {
			uni.setNavigationBarTitle({
				title:uni.getStorageSync('project_title')
			})
			this.autoLogin();
			if(options.scene){
				this.scene = options.scene;
			}
			
			
		},
		methods: {
			
			login() {
				
				var postData = {};
				postData.scene = this.scene;
				this.loginLoading = true;
				this.$http.requestApi('POST', '/user/confirmPcLogin', postData).then(res => {
					const resData = res.data;
					const resCode = res.code;
					this.loginLoading = false;
					if (resCode == 0) {
						
						uni.showToast({
							title: '登录成功！',
							icon: 'none'
						});
						uni.reLaunch({
							url: "/pages/index/index",
						})
					
					} else {
						uni.showToast({
							title: '登录失败，请稍后重试！',
							icon: 'none'
						});
					}
				})
			},
			autoLogin(){
				let token = uni.getStorageSync('token');
				if (token == '' || token == undefined) {
					var that = this;
					uni.login({
						provider: "weixin",
						success: (res) => {
							var postData = {};
							postData.code = res.code;
							that.$http.requestApi('POST', '/user/wxMiniLogin', postData).then(result => {
								const resData = result.data;
								const resCode = result.code;
								if (resCode == 0) {
									uni.setStorageSync('token', resData.accessToken);
									uni.setStorageSync('userInfo', resData.userInfo);
								}else{
									uni.navigateTo({
										url: '/pages/my/login'
									});
								}
							})
						},
					});
				}
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