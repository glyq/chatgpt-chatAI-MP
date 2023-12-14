<template>
	<view class="container">

		<view style="padding: 0 20rpx;">
			 
			
			
			
			
			<view  class="img-info-box">
				<view   class="img-info-item"   v-for="(buy, index) in buyList" :key="index">
					<view class="img-info-item__title">
						<view class="img-info-item-title__title">{{buy.title}}</view>
						<view class="img-info-item-title__desc">{{buy.desc}}</view>
					</view>
					<view v-if="buy.type == 1" class="img-info-item__content">
						<u-button size="normal" icon="gift" iconColor="#1acc89" color="#e7f9f3" :text="buy.botton"
							:disabled="buy.disabled" shape="circle"
							:customStyle="{color:'#1acc89',height:'66rpx',fontSize:'26rpx'}" @click="buy(buy.id)">
						</u-button>
					</view>
					
					<view v-if="buy.type == 2" class="img-info-item__content">
						<u-button size="normal" icon="star" iconColor="#1acc89" color="#e7f9f3" :text="buy.botton"
							:disabled="buy.disabled" shape="circle"
							:customStyle="{color:'#1acc89',height:'66rpx',fontSize:'26rpx'}" @click="share1(buy.id)">
						</u-button>
					</view>
					
					<view v-if="buy.type == 3" class="img-info-item__content">
						<u-button size="normal" icon="share" iconColor="#1acc89" color="#e7f9f3" :text="buy.botton"
							:disabled="buy.disabled" shape="circle"  open-type="share"
							:customStyle="{color:'#1acc89',height:'66rpx',fontSize:'26rpx'}" >
						</u-button>
					</view>
					
					<view v-if="buy.type == 4" class="img-info-item__content">
						<u-button size="normal" icon="play-right" iconColor="#1acc89" color="#e7f9f3" :text="buy.botton"
							:disabled="buy.disabled" shape="circle"    :loading="isLoading" @click="showAd(buy.id)"
							:customStyle="{color:'#1acc89',height:'66rpx',fontSize:'26rpx'}" >
						</u-button>
					</view>
					
					<view v-if="buy.type == 5" class="img-info-item__content">
						<u-button size="normal" icon="attach" iconColor="#1acc89" color="#e7f9f3" :text="buy.botton"
							 shape="circle" @click="goWxApp(buy)"
							:customStyle="{color:'#1acc89',height:'66rpx',fontSize:'26rpx'}" >
						</u-button>
					</view>
					
					
					<view v-if="buy.type == 6" class="img-info-item__content">
						<u-button size="normal" icon="share-square" iconColor="#1acc89" color="#e7f9f3" :text="buy.botton"
							 shape="circle" @click="goPopup(buy)"
							:customStyle="{color:'#1acc89',height:'66rpx',fontSize:'26rpx'}" >
						</u-button>
					</view>
					
					<view v-if="buy.type == 7" class="img-info-item__content">
						<u-button size="normal" icon="server-man" iconColor="#1acc89" color="#e7f9f3" :text="buy.botton"
							:disabled="buy.disabled" shape="circle"  open-type="contact"
							:customStyle="{color:'#1acc89',height:'66rpx',fontSize:'26rpx'}" >
						</u-button>
					</view>
					
					
					
					
				</view>
				
				
				
			</view>
			<u-popup  mode="center" :show="showPopup" @close="closePopup" >
				<view >
					<u--image width="300" height="300"  :src="popupSrc" ></u--image>
				</view>
			</u-popup>
		</view>
		
		<view>
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
				userInfo: {},
				isLoading: false,
				buyList:{},
				lookId:0,
				ad:uni.getStorageSync('ad'),
				_rewardedVideoAd:{},
				showPopup:false,
				popupSrc:'',
				adVideo: this.$config.adVideo
			}
		},
		onLoad() {
			uni.setNavigationBarTitle({
				title:uni.getStorageSync('project_title')
			})
		},
		onReady() {
		      this.isLoading = true;
				var rewardedVideoAd = this._rewardedVideoAd = uni.createRewardedVideoAd({
					  adUnitId : this.$config.adReward
				  });
				rewardedVideoAd.onClose((res) => {
				  if (res && res.isEnded) {
				        this.look()
				      } else {
				        uni.showToast({
				        	title: '未观看完成，未获取奖励',
				        	icon: 'none'
				        });
				      }
				});
				rewardedVideoAd.onLoad(() => {
				    this.isLoading = false
				});
				rewardedVideoAd.onError((err) => {
				    uni.showToast({
				    	title: '广告加载失败，重新登录重试',
				    	icon: 'none'
				    });       
				})
		},
		onShow(){
			this.getBuyList();
		},
		
		
		methods: {
			
			showAd(id) {
				this.lookId = id;
				if (this.isLoading) {
				  return
				}
				this._rewardedVideoAd.show();
			},
			
			async getBuyList(){
				var res = await this.$http.requestApi('GET', '/user/getBuyList',{showLoading:false});
				this.buyList = res.data;
			},
			
			share1(id){
				uni.navigateTo({
					url: '/pages/my/share?id='+id
				});
			},
			look(){
				this.$http.requestApi('POST', '/user/addNums',{id:this.lookId}).then(res => {
					
					if(res.code == 0){
						uni.showToast({
							title: '观看完成，已获取奖励',
							icon: 'none'
						});
					}else{
						uni.showToast({
							title: res.message ? res.message : '观看完成，奖励获取失败',
							icon: 'none'
						});
					}
					this.getBuyList();
					
				});
			},
			goWxApp(options){
				uni.navigateToMiniProgram({
					appId: options.appid,
					path: options.path,
					fail() {}
				})
			},
			goPopup(options){
				this.popupSrc = options.img;
				this.showPopup = true;
			},
			closePopup(){
				this.showPopup = false;
			},
			buy(id){
			
				var res = this.$http.requestApi('POST', '/user/buy', {
					id: id,
				}).then(res => {
					
					
					if(res.code != 0){
						uni.showToast({
							title: res.message ? res.message : '支付失败',
							icon: 'none'
						});
						return;
					}
					
					var type = res.data.type;
					var payData = res.data.data;
					
					if(type == 2){
						var out_trade_no = payData.order_id;
						var total_fee = payData.total_fee;
						var body = payData.body;
						var title = payData.title;
						var attach = '';
						var notify_url = payData.notify_url;
						this.$wxpay.toPay(out_trade_no,total_fee,body,notify_url,attach,title,(response)=>{
							uni.navigateBack({
							    delta: 2
							})
						});
					}
					
					if(type == 1 || type == 3){
						
						uni.requestPayment({
						    "provider": "wxpay", 
						    "timeStamp":payData.timeStamp,
						    "nonceStr":payData.nonceStr,
						    "package":payData.package,
						    "signType":payData.signType,
						    "paySign":payData.paySign,
						    success(result) {
								uni.navigateBack({
								    delta: 2
								})
							},
						    fail(e) {
								if(res.code == 0){
									uni.showToast({
										title: '支付取消',
										icon: 'none'
									});
									return;
								}
							
							}
						})
					}
					
				});
				
				
				
				
			}
			
		
		}
	}
</script>

<style lang="scss">
	
	.container{
		margin-top: 50rpx;
	}
	
	


	.img-info-box {
		font-size: 28rpx;
		backdrop-filter: blur(30px);
		-webkit-backdrop-filter: blur(30px);
		background-color: rgba(255, 255, 255, 0.8);
		padding: 30rpx;
		margin-bottom: 20rpx;
		border-radius: 12rpx;

		.img-info-item {
			display: flex;
			justify-content: space-between;
			padding: 20rpx 0;
			align-items: center;

			.img-info-item__title {
				line-height: 50rpx;

				.img-info-item-title__title {
					font-size: 28rpx;
					color: #222;
				}

				.img-info-item-title__desc {
					font-size: 24rpx;
					color: #999;
				}
			}

			.img-info-item__content {
				width: 160rpx;
			}
		}
	}
</style>