<template>
	<view>
	
		<u-sticky v-if='msg.msg'>
			<u-notice-bar direction="row" :text="msg.msg" linkType="navigateTo" :url="msg.path"
				 mode="link" speed="60" bgColor="#e7f9f3" color="#1acc89">
			</u-notice-bar>
		</u-sticky>
		
		
		
		<view class="container">
			<view class="pre-form">
				
					<view class="option-item" @click="assistantList(assistant_id)">
						<view class="option-content">
							<view class="sd-model-item sd-model-item-after">
								<view class="sd-item">
									<view style="border-radius: 6px;">
										<u--image :src="assistant_img" radius="6" width="100rpx"
											height="100rpx" :lazyLoad="false"
											>
										</u--image>
									</view>
									<view class="sd-item-context">
										<view class="sd-item-title sd-item-title-weight">
											{{assistant_name}}
											
										</view>
										<view class="sd-item-tips">
											{{assistant_desc}}
											
										</view>
									</view>
									
								</view>
							</view>
						</view>
					</view>
					
					
					<view class="option-item" >
						
						<view v-for="(val, index) in inputItem" :key="index">
							
						<view class="title layout">
							<text style="z-index: 1;" class="title-item">{{val.title}}</text>
						</view>
						
						<view v-if="val.type=='input'" class="option-content">
							<view  class="sd-model-item-context">
								
								<u--textarea v-model="val.val"
									:placeholder="val.default"  autoHeight  count maxlength="20" confirmType="done"
								>
								</u--textarea>
								
							</view>
						</view>
						<view v-if="val.type=='textarea'" class="option-content">
							<view  class="sd-model-item-context">
								<u--textarea v-model="val.val"
									:placeholder="val.default"  height="100"  count maxlength="400" confirmType="done"
								>
								</u--textarea>
							</view>
						
						</view>
						
						<view  v-if="val.type=='slider'" class="option-content">
								
								<u-slider activeColor="#1acc89" v-model="val.val" step="50" min="50" max="1000" showValue></u-slider>
						</view>
						
						</view>
						<view v-if="streamShow">
							<view class="title layout">
								<text style="z-index: 1;" class="title-item">流式输出</text>
							</view>
							<u-switch activeColor="#1acc89" v-model="streamDefault" @change="streamChange"></u-switch>
						</view>
					</view>
					
				
			</view>
		</view>
		
		
		<view><u-button color="#1acc89" @click="submit"  type="primary" size="normal" text="生成" ></u-button></view>
		
		<view v-if='ad' style="position:relative; bottom: 0;margin-top: 20rpx">
			<ad v-if="ad" ad-type="video" :unit-id="adVideo"></ad>
		</view>
	</view>
	
</template>

<script>
	export default {
		components: {
			
		},
		data() {
			return {
				stream:0,
				inputItem:[],
				assistant_id:0,
				assistant_img:'',
				assistant_name:'',
				assistant_desc:'',
				msg:[],
				ad:uni.getStorageSync('ad'),
				streamShow:0,
				streamDefault:true,
				adVideo: this.$config.adVideo
				
				
			}
		},
		
		onLoad(options) {
			uni.setNavigationBarTitle({
				title:uni.getStorageSync('channel_info').name
			})
			this.getAssistantInfo(options.assistant_id || 0);
			this.getMsg()
		},
		methods: {
			async getMsg(){
				var res = await this.$http.requestApi('GET', '/index/getMsg',{position:2});
				this.msg = res.data;
			},
			async getAssistantInfo(assistant_id){
				var data = {};
				data.assistant_id = assistant_id;
				var res = await this.$http.requestApi('GET', '/cate/assistantInfo',data);
				this.assistant_img = res.data.icon;
				this.assistant_desc = res.data.desc;
				this.assistant_name = res.data.name;
				this.assistant_id = res.data.id;
				this.inputItem = res.data.keywords;
				this.streamShow = res.data.stream.show;
				
				
				var stream = uni.getStorageSync('stream');
				if(stream == 2){
					this.streamDefault = true;
				}else if(stream == 1){
					this.streamDefault = false;
				}else{
					this.streamDefault = res.data.stream.default;
				}
				this.streamChange(this.streamDefault);
	
			},
			assistantList(assistant_id){
				uni.navigateTo({
					url: '/pages/assistant/index?assistant_id='+assistant_id
				});
			},
			
			checkToken() {
				const token = uni.getStorageSync('token');
				if (token == '' || token == undefined) {
					return false
				} else {
					return true
				}
			},
			streamChange(e){
				if(e){
					uni.setStorageSync('stream',2)
					this.stream = 2;
				}else{
					uni.setStorageSync('stream',1)
					this.stream = 1;
				}
			},
			
			
			submit() {
				let islogin = this.checkToken();
				if (!islogin) {
					uni.navigateTo({
						url: '/pages/my/login'
					});
					return false;
				}
				
				
				let post = {
					input:JSON.stringify(this.inputItem),
					assistant_id:this.assistant_id,
					loadingData:'生成中'
				};
				
				if(this.streamShow){
					post.stream = this.stream
				}
				
				this.$http.requestApi('POST', '/chat/create/', post).then(res => {
					
					const resCode = res.code;
					const resData = res.data;
					if (resCode == 2009) {
						uni.showModal({
							title: '温馨提示',
							content: '您的可用生成次数不足，可通过以下方式免费获取次数哦',
							confirmText: '每日任务',
							success: function(res) {
								if (res.confirm) {
									uni.navigateTo({
										url: '/pages/my/buy'
									});
								} else if (res.cancel) {
									
								}
							}
						});
						return false;
					}
					if (resCode == 0) {
						
						var stream = resData.stream;
						var chatId = resData.chat_id;
						uni.navigateTo({
							url: '/pages/chat/chat?chatId='+chatId+'&stream='+stream+'&assistant='+this.assistant_name
						});
					} else {
						uni.showToast({
							title: res.message ? res.message : '未知错误',
							duration: 2000,
							icon: 'none'
						});
					}
				})
			}
		}
	}
</script>

<style lang="scss" scoped>
	
	.container {
		width: 92%;
		margin: 30rpx auto;

		

		.pre-form {
			margin: 20rpx 0;
		}

		
	}

	.option-item {
		.title {
			padding: 30rpx 0;
			font-size: 30rpx;
			font-weight: 600;
		}

		.option-content {

			.sd-model-item {
				margin-bottom: 20rpx;
				background-color: #FAFAFA;
				padding: 20rpx;
				color: #222;
				border-radius: 12rpx;
				position: relative;

				.sd-item {
					display: flex;

					.sd-item-context {
						margin: 0 30rpx;
						display: flex;
						flex-direction: column;
						justify-content: center;
						line-height: 48rpx;

						.sd-item-title {
							font-size: 30rpx;
						}

						.sd-item-title-weight {
							font-weight: 600;
						}

						.sd-item-tips {
							font-size: 24rpx;
							color: #999;
							line-height: initial;
						}

						.sd-item-font {
							font-size: 28rpx;
						}
					}
				}
			}

			.sd-model-item-after {
				.sd-item::after {
					content: '';
					position: absolute;
					right: 30rpx;
					top: calc(50% - 12rpx);
					width: 20rpx;
					height: 20rpx;
					border-top: 4rpx solid;
					border-right: 4rpx solid;
					border-color: #999;
					content: '';
					transform: rotate(45deg);
				}
			}
		}
	}

	.layout {
		
		display: flex;
		justify-content: space-between;
		align-items: center;
		.tips-item {
			font-weight: 400;
			font-size: 24rpx;
			color: #999;
		}
	}

	
</style>