<template>
	<view class="chat">
		<u-navbar :title="title" :autoBack="false" titleStyle="font-weight: 600">
			<view  class="u-nav-slot" slot="left">
				<view v-if="streamShow" style="display: flex;justify-content: space-between;align-items: center;">
					<text style="font-size: 30rpx;font-weight: 600;margin-right: 15rpx;">流式输出</text>
					<u-switch activeColor="#1acc89" :disabled="creating" v-model="streamDefault" @change="streamChange"></u-switch>
				</view>
			</view>
		</u-navbar>
		
		<mescroll-body top="100" :auto="false" @init="mescrollInit" :down="downOption" @down="downCallback"
			:up="upOption" @up="upCallback">
			
			<view style="padding-bottom: 30px;padding-top: 40px;">
				<view class="guide">
					<view class="guide_title" >
						{{welcome.title}}
					</view>
					<view class="guide_text">
						{{welcome.info}}
					</view>
					
					<view @click="welcomeSend(welcomeItem)" class="guide_select" v-for="welcomeItem, index in welcome.list" :key="index">
						<text>{{welcomeItem}}</text>
						<u-icon  size="14" name="arrow-right" color="#9f9f9f"></u-icon>
					</view>
					
				</view>
			</view>
			
			
			<view class="chat-item" v-for="item, index in chat" :key="index">
				
				<u-transition :show="true" mode="fade-right">
					<view class="chat-item__right">
						<view class="chat-item__right-message" @longtap="copy(item.question)">
							{{ item.question }}
						</view>
						<u-avatar shape="circle" size="35" :src="userInfo.head_img"></u-avatar>
					</view>
				</u-transition>
				
				<u-transition :show="true" mode="fade-left" >
					<view class="chat-item__left u-flex">
						<u-avatar size="35" shape="circle"></u-avatar>
						<view class="chat-item__left-right">
							<view class="chat-item__left-name"> {{channel.name}} </view>
							<view class="chat-item__left-bottom">
								<view class="chat-item__left-message" @longtap="copy(item.answer)">
									<u-loading-icon v-if="item.answer == 'creating' && stream==1"></u-loading-icon>
									<text v-else-if="item.answer == 'creating' && stream==2">思考中...</text>
									<zero-markdown-view v-else :markdown="item.answer ? item.answer : '输出被打断啦，请稍后重新进入小程序再看看吧'" @customEvent="handleCustomEvent"></zero-markdown-view>
								</view>
								
								<view style="margin-top:auto;padding: 10rpx 0;float: right;margin-right: 20rpx;">
									<u-icon v-if="item.answer  && !creating"
										@tap="copy(item.answer)" name="file-text" size="18">
									</u-icon>
									<u-loading-icon v-if="creating && index==chat.length-1 && stream==2" size="18"></u-loading-icon>
								</view>
							</view>
						</view>
					</view>
				</u-transition>
			</view>
			<view class="seize" style="height: 200rpx"></view>
		</mescroll-body>
		
		<view class="input-box">
			<view><u-icon v-if="creating" size="38" name="reload" color="#9f9f9f"></u-icon></view>
			<view><u-icon v-if="!creating" size="38" name="reload" color="#1acc89" @click="reload"></u-icon></view>
			<view style="flex: 1;margin-right: 30rpx;margin-left: 10rpx;">
				<u--input  @confirm="send" confirmType="send" :disabled="creating" shape="circle" placeholder="有问题尽管问我~" border="surround" v-model="question" cursorSpacing="10"  maxlength="500">
				</u--input>
			</view>
			<view>
				<u-icon v-if="creating && stream==1" color="#9f9f9f" size="38" name="play-circle-fill"></u-icon>
				<u-icon v-if="creating && stream==2" color="#1acc89" size="38" name="pause-circle-fill" @click="finish"></u-icon>
				
				<u-icon v-if="!creating" color="#1acc89" size="38" name="play-circle-fill" @click="send"></u-icon>
			</view>
		</view>
	</view>

</template>

<script>
	
	import MescrollBody from "mescroll-uni/mescroll-body.vue";

	export default {
		
		components: {
			MescrollBody
		},
		
		data() {
			return {
				userInfo: uni.getStorageSync('userInfo'),
				mescroll: null,
									
				upOption: {
					use: false,
				},
				downOption: {
					auto: false,
					textInOffset:'下拉加载',
					beforeEndDelay:1000,
					bgColor:'white',
					textColor:'black'
				},
				chat : [],
				page : 0,
				sessionId : 0,
				question : '',
				creating : 0,
				stream : 1,
				welcome : {
					title : '你好，我是你的智能助手',
					info : '作为你的智能伙伴，我既能写文案、想点子，又能陪你聊天、答疑解惑。你可以试着问我：',
					list : [
						'请帮我生成几段适合发给老板、朋友、同事、家人、客户的2024拜年祝福。',
						'请分别帮我机智幽默地回怼以下几个来自亲戚的问题：1、一年挣多少钱？2、什么时候结婚？3、什么时候生小孩？',
						'请帮我生成一对春联，要表达出吉祥、团圆、步步高升的意思。'
					]
				},
				streamDefault:true,
				title : '对话',
				streamShow : 0,
				channel:uni.getStorageSync('channel_info')
				
			}
		},
		
		onLoad(options) {
			
			this.streamSelect();
			this.downCallback();
		},
		watch : {
			creating(newVal){
				if(newVal==1){
					uni.hideTabBar();
				}else{
					uni.showTabBar();
				}
			}
		},
		
		methods: {
			streamSelect(){
				var channel = this.channel;
				this.streamShow = channel.stream_show;
				
				var stream = uni.getStorageSync('stream');
				if(stream == 2){
					this.streamDefault = true;
				}
				if(stream == 1){
					this.streamDefault = false;
				}
				if(!stream){
					this.streamDefault = channel.stream_default;
				}
				
				if(!this.streamShow){
					this.stream = channel.stream ? channel.stream : 1;
					uni.setStorageSync('stream',0)
				}else{
					this.streamChange(this.streamDefault);
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
			welcomeSend(msg){
				this.question = msg;
				this.send();
			},
			handleCustomEvent(data) {
				if(this.page == 1){
					this.$nextTick(() => {
						this.mescroll.scrollTo(99999999);
					});
				}
			},
			mescrollInit(mescroll) {
				this.mescroll = mescroll;
			},
			async downCallback(){
				this.page += 1;
				if(!this.sessionId){
					var session = await this.$http.requestApi('GET', '/dialog/getActiveSession');
					this.sessionId = session.data.id;
				}
				
				
				var res = await this.$http.requestApi('GET', '/dialog/getDialog',{id: this.sessionId,page:this.page,pageSize:10});
				if(res.code!=0){
					this.mescroll.endErr();
					return;
				}
				
				let curPageData = res.data.data;
				
				if(this.page == 1){
					this.chat = [];
				}

				this.chat = curPageData.concat(this.chat);

				this.mescroll.endSuccess(10); 
				
				this.mescroll.lockDownScroll(true)
				if(this.page < res.data.totalPage){
					this.mescroll.lockDownScroll(false)
				}	
				
				
				
			},
			
			upCallback(options){
				
				
			},
			
			copy(val) {
				if(this.creating){
					return false;
				}
				uni.setClipboardData({
					data: val,
					success: function() {
						uni.showToast({
							title: '复制成功',
							icon: 'none'
						})
					}
				});
			},
			
			send(){
				var that = this;
				this.creating = 1;
				if (!this.question) {
					uni.showToast({
						title: '你还没有输入内容呢！',
						icon: 'none'
					});
					this.creating = 0;
					return
				}
				
				this.chat.push({
					question: this.question,
					answer: 'creating'
				});
			
				this.$nextTick(() => {
					this.mescroll.scrollTo(99999999);
				});
				
				
				let post = {
					content:this.question,
					sessionId:this.sessionId,
					showLoading : 0
				};
				
				this.question = '';
				
				if(this.streamShow){
					post.stream = this.stream
				}
				
				this.$http.requestApi('POST', '/dialog/createDialog/', post).then(res => {
					
					const resCode = res.code;
					const resData = res.data;
					if (resCode == 2009) {
						that.creating = 0;
						that.chat.pop();
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
						
						if(stream == 1){
							that.chat[this.chat.length-1]['answer'] = resData.data;
							that.creating = 0;
							setTimeout(function(){
								that.$nextTick(() => {
									that.mescroll.scrollTo(99999999,300);
								});
							},300)
							
							
						}
						
						if(stream == 2){
							this.chatStream(chatId)
						}
						
						

					} else {
						that.creating = 0;
						uni.showToast({
							title: res.message ? res.message : '未知错误',
							duration: 2000,
							icon: 'none'
						});
						that.chat.pop();
					}
				})

			},
			finish(){
				this.creating = 0;
				uni.closeSocket();
			},
			chatStream(chatId){
				
				uni.closeSocket();
				
				this.$scoket.connectApi();
			
				let post = {
					chatId:chatId,
				};
				
				var thats = this;
				
				uni.onSocketOpen(function (res) {
					thats.$scoket.sendSocketMessage(post);
				});
			
				uni.onSocketClose(function(res) {
					thats.creating = 0;
				});
				
				
				uni.onSocketMessage(function(res) {
					thats.$nextTick(() => {
						thats.mescroll.scrollTo(99999999);
					});
					var json = JSON.parse(res.data);
					
					if(json.code != 0){
						uni.showToast({
							title: json.message ? json.message : '生成失败',
							icon: 'none',
							duration:3000
						});
						
					}
					thats.chat[thats.chat.length-1]['answer'] = json.data;
			
					
				});
			},
			reload(){
				var that = this;
				uni.showModal({
					title: '确定换个话题吗？',
					content: '对话内容将被删除，且无法撤销',
					confirmText: '确定',
					cancelText: '取消',
					success: function(res) {
						if (res.confirm) {
							that.newSession();
						} else if (res.cancel) {
							
						}
					}
				});
			},
			newSession(){
				var that = this;
				this.$http.requestApi('GET', '/dialog/mpDeleteSession',{id:this.sessionId}).then(res=>{
					if(res.code != 0){
						uni.showToast({
							title: res.message ? res.message : '未知错误',
							duration: 2000,
							icon: 'none'
						});
						return false;
					}
					that.chat = [];
					that.page = 1;
					that.sessionId = 0;
					that.downCallback();
				});
				
			}
		}
	}
</script>

<style lang="scss" scoped>
	.chat {
		padding: 20rpx;
		box-sizing: border-box;

		&-item {
			margin-bottom: 20rpx;

			&__left {
				display: flex;
				margin-top: 20rpx;

				&-right {
					margin-left: 20rpx;
					overflow-x: auto;
				}

				&-name {
					font-size: 24rpx;
				}

				&-message {
					margin-top: 10rpx;
					background: #f6f6f6;
					padding: 20rpx;
					border-radius: 6rpx;
					font-size: 28rpx;
					margin-right: 20rpx;
				}
			}

			&__right {
				display: flex;
				margin-top: 20rpx;
				justify-content: flex-end;

				&-message {
					margin-right: 20rpx;
					background: #1acc89;
					padding: 20rpx;
					border-radius: 6rpx;
					font-size: 28rpx;
					color: #fff;
				}
			}
		}
	}

	.input-box {
		display: flex;
		background: #fff;
		position: fixed;
		bottom: 0;
		height: calc(env(safe-area-inset-bottom) + 50px);
		height: calc(constant(safe-area-inset-bottom) + 50px);
		left: 0;
		width: 100%;
		padding: 20rpx;
		box-sizing: border-box;
		justify-content: space-between;
		align-items: center;
	}
	
	.guide {
		background-color: white;
		margin-left: 20px;
		margin-right: 20px;
		padding: 10px;
		border-radius: 5px;
	}
	
	.guide_title {
		font-size: 16px;
		font-weight: bold;
		padding-top: 10px;
		padding-bottom: 10px;
	}
	
	.guide_text {
		font-size: 14px;
		color: #575659;
		margin-bottom: 10px;
	}
	
	.guide_select {
		margin-top: 4px;
		display: flex;
		padding: 10px;
		background-color: #f5f6f8;
	}
	
	.guide_select text {
		width: 100%;
		font-size: 14px;
		font-weight: 600;
	}
	
</style>