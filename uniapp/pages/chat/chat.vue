<template>
	<view>
		
		
		<view class="container">
			<view class="title"><u--text bold size="20" lineHeight="20" :text="assistant"></u--text></view>
			<u-line color="#1acc89"></u-line>
			<view class="content">
				<view class="list">
					
					
<u--textarea autoHeight="true" maxlength="-1" v-if="edit" :value="list" ></u--textarea>
</view>


				<view class="processed-context">
					<u-loading-icon :show="creating" text="生成中..."></u-loading-icon>
					<u--text v-if="!edit" size="15" lineHeight="20" :text="list"></u--text>
				</view>
				
				
				<view v-if="!creating" class="info">
					<view class="tag"><u-button @click="copy" :icon="cdnUrl+'/copy.png'"  text="复制全文"></u-button>
</view>
					<view  class="tag">
						<u-button v-if="!edit" @click="editClick" color="#1acc89" :icon="cdnUrl+'/edit.png'"  text="编辑内容"></u-button>
						<u-button v-if="edit" @click="editClick" color="#1acc89" :icon="cdnUrl+'/cancel.png'"  text="取消编辑"></u-button>
						</view>
				</view>
				
				<view v-if="creating && stream==2" class="info">
						<view class="finish"><u-button @click="finish" :icon="cdnUrl+'/stop.png'" text="终止生成"></u-button>
				</view>
				</view>
				
				
			</view>
			<view v-if="ad" style="position:relative; bottom: 0;margin-top: 200rpx">
				<ad v-if="ad" ad-type="video" :unit-id="adVideo"></ad>
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
				creating:1,
				stream :1,
				edit:0,
				list : '',
				assistant:'',
				ad:uni.getStorageSync('ad'),
				socketMsgQueue : [],
				cdnUrl:this.$config.cdnUrl,
				adVideo: this.$config.adVideo
				
			
			}
		},
		onLoad(options){
			uni.setNavigationBarTitle({
				title:uni.getStorageSync('channel_info').name
			})
			this.stream = options.stream;
			this.assistant = options.assistant;
			if(options.stream == 1){
				this.chat(options.chatId);
			}
			
			if(options.stream == 2){
				this.chatStream(options.chatId);
			}
			
		
			
		},
		methods: {
			editClick(data){
				if(this.edit==1){
					this.edit=0;
				}else{
					this.edit=1;
				}
			},
			copy() {
				uni.setClipboardData({
					data: this.list,
					success: function() {
						uni.showToast({
							title: '复制成功',
							icon: 'none'
						})
					}
				});
			},
			chat(chatId){
				var postData = {};
				postData.chatId = chatId;
				postData.loadingData = '生成中';
				this.$http.requestApi('POST', '/chat/getChat', postData).then(res => {
					const resData = res.data;
					const resCode = res.code;
					if (resCode == 0) {
						this.list = resData.msg;
						this.assistant = resData.assistant;
					} else {
						uni.showToast({
							title: '生成失败，请稍后重试！',
							icon: 'none'
						});
					}
					this.creating = 0;
					
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
					
					var json = JSON.parse(res.data);
					
					thats.list = json.data;
					
					if(json.code != 0){
						uni.showToast({
							title: json.message ? json.message : '生成失败',
							icon: 'none',
							duration:10000
						});
						
					}
	
					
					
				});
			}
		}
	}
</script>

<style lang="scss">
	.container{
		width: 100%;
	}
	.title{
		margin: 20rpx;
	}
	.content{
		margin: 20rpx;
		.processed-context {
			background-color: #e7f9f3;
			padding: 30rpx;
			border-radius: 12rpx;
			min-height: 200rpx;
		}
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
