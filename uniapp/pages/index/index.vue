<template>
	<view class="">
		
		<u-sticky v-if='msg.msg'>
			<u-notice-bar direction="row" :text="msg.msg" linkType="navigateTo" :url="msg.path"
				 mode="link" speed="60" bgColor="#e7f9f3" color="#1acc89">
			</u-notice-bar>
		</u-sticky>

		<view class="container">
			
			<view class="swiper-box" v-if="swiperList">
				<swiper class="swiper" circular="true" autoplay="true" @change="swiperChange">
					<swiper-item v-for="swiper in swiperList" :key="swiper.id">
						<view class="swiper-item">
							<image :src="swiper.img" @click="toSwiper(swiper)" mode="widthFix"></image>
						</view>
					</swiper-item>
				</swiper>
				<view class="indicator">
					<view class="dots" v-for="(swiper, index) in swiperList" :key="index"
						:style="{'--length': swiperList.length}" :class="[currentSwiper >= index ? 'on' : '']">
					</view>
				</view>
			</view>
			
			
			<u-tabs :list="cate"   @change="tabsChange"
			lineWidth="30"
			        lineColor="#1acc89"
			        :activeStyle="{
			            color: '#303133',
			            fontWeight: 'bold',
			            transform: 'scale(1.05)'
			        }"
			        :inactiveStyle="{
			            color: '#606266',
			            transform: 'scale(1)'
			        }"
			        itemStyle="padding-left: 15px; padding-right: 15px; height: 34px;"
			>
			<view
			                slot="right"
			                style="padding-left: 4px;"
			                @tap="assistantList"
			        >
			            <u-icon
			                    name="list"
			                    size="21"
			                    bold
			            ></u-icon>
			        </view>
				
			</u-tabs>
			
			
			<view class="cateList">
				
				<view class="list">

					<block v-for="(row, index) in cateInfo" :key="index">
						<view class="column" @click="info(row)">
							<view>
								<view class="top">
									<view class="title">{{row.name}}</view>
								</view>
								<view class="left">
									<view class="desc">{{row.desc}}</view>
								</view>
							</view>
							<view class="right">
								<image :src="row.icon"></image>
							</view>
						</view>
						
						<view style="margin-top: 10px;"></view>
					</block>
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
				cate:[],
				cateId:0,
				cateInfo: [],
				currentSwiper: 0,
				swiperList: [],
				msg:[]
				
			};
		},
		onLoad(options) {
			uni.setNavigationBarTitle({
				title:uni.getStorageSync('channel_info').name
			})
			
			this.autoLogin();
			
			if(options.pid != undefined){
				
				var _that = this;
				uni.showModal({
					title: '为好友助力',
					content: '每位用户最多可为3位好友助力哦',
					confirmText: '助力',
					success: function(res) {
						if (res.confirm) {
							_that.showHelp(options.pid);
						} else if (res.cancel) {
							
						}
					}
				});
			}
			
			
			this.getList();
			this.getCate();
			this.getMsg();
			this.showAd();
		},
		
		methods: {
			async getCate(){
				var res = await this.$http.requestApi('GET', '/cate/cateList');
				var cate = res.data;
				this.cate = cate;
				this.getCateInfo(cate[0].id);
				
			},
			async getCateInfo(cateId){
				var data = {};
				data.cate_id = cateId;
				var res = await this.$http.requestApi('GET', '/cate/assistantList',data);
				this.cateInfo = res.data;
			},
			
			async getList(){
				var res = await this.$http.requestApi('GET', '/index/getList');
				this.swiperList = res.data.swiper;
			},
			
			async getMsg(){
				var res = await this.$http.requestApi('GET', '/index/getMsg',{position:1});
				this.msg = res.data;
			},
			
			async showAd(){
				var res = await this.$http.requestApi('GET', '/user/showAd');
				uni.setStorageSync('ad', res.data.show);
			},
			
			
			tabsChange(data) {
				
				var cateId = data.id;
			    this.getCateInfo(cateId);
			},
			assistantList(){
				uni.navigateTo({
					url: '/pages/assistant/index'
				});
			},
			info(data){
				if(data.apppath == ''){
					uni.reLaunch({
						url: '/pages/create/index?assistant_id='+data.id,
					});
				}else{
					uni.navigateToMiniProgram({
						appId: data.appid,
						path: data.path,
						fail() {}
					})
				}
				
			}
			,
			async showHelp(pid){
				var _res = await this.$http.requestApi('POST', '/user/help', {
					needLogin:1,
					pid:pid
				});
				if(_res.code == 0){
					uni.showToast({
						title: '助力成功！',
						icon: 'none'
					});
				}else{
					uni.showToast({
						title: _res.message,
						icon: 'none'
					});
				}
			},
			
			
			//轮播图跳转
			toSwiper(e) {
				if(e.jump_type == 1){
					uni.navigateTo({
						url: '/' + e.path
					});
				}else{
					uni.navigateToMiniProgram({
						appId: e.appid,
						path: e.path,
						fail() {}
					})
				}
			},
			
			
			swiperChange(event) {
				this.currentSwiper = event.detail.current;
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
								}
							})
						},
					});
				}
			}
		}
	};
</script>
<style lang="scss">
	page {
		background-color: #fff;
		padding-bottom: 30rpx;
		
	}

	.container {
		width: 92%;
		position: relative;
		overflow: scroll;
		 z-index: 10;
		margin: 10rpx auto;
	}

	.swiper-box {
		margin-top: 6rpx;
		display: flex;
		justify-content: center;
		height: 300rpx;
		position: relative;
		 z-index: 1;
		overflow: hidden;
		border-radius: 14rpx;

		.swiper {
			width: 100%;
			height: 100%;

			.swiper-item {
				image {
					width: 100%;
					height: 100%;
					border-radius: 14rpx;
				}
			}
		}

		.indicator {
			position: absolute;
			bottom: 20rpx;
			left: 20rpx;
			background-color: rgba(255, 255, 255, 0.4);
			width: 150rpx;
			height: 5rpx;
			border-radius: 3rpx;
			overflow: hidden;
			display: flex;

			.dots {
				width: 0rpx;
				background-color: rgba(255, 255, 255, 1);
				transition: all 0.3s ease-out;

				&.on {
					width: calc(100% / var(--length));
				}
			}
		}
	}


	.cateList {
		margin: 20rpx auto;

		.text {
			width: 100%;
			height: 80rpx;
			font-size: 34rpx;
			font-weight: 600;
			margin-top: -10rpx;
		}
		
		.list {
			width: 100%;
			display: flex;
			justify-content: space-between;
			flex-wrap:wrap;
			
			.column {
				margin-bottom: 20rpx;
				width: 42.5%;
				padding: 3%;
				background-color: #FAFAFA;
				border-radius: 14rpx;
				overflow: hidden;
				display: flex;
				justify-content: space-between;
				flex-wrap: wrap;

				.top {
					width: 100%;
					display: flex;
					align-items: center;
					margin-bottom: 5rpx;
					line-height: 60rpx;

					.title {
						font-size: 30rpx;
					}

					
				}

				.left {
					width: 100%;
					display: flex;
					flex-wrap: wrap;
					align-content: space-between;

					.desc {
						margin-top: 5rpx;
						width: 100%;
						font-size: 22rpx;
						color: #acb0b0;
					}
				}

				.right {
					width: 80rpx;
					height: 80rpx;

					image {
						width: 80rpx;
						height: 80rpx;
					}
				}
			}
		}
	}
</style>