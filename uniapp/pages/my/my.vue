<template>
	<view>
	
		<u-sticky v-if='msg.msg'>
			<u-notice-bar direction="row" :text="msg.msg" linkType="navigateTo" :url="msg.path"
				 mode="link" speed="60" bgColor="#e7f9f3" color="#1acc89">
			</u-notice-bar>
		</u-sticky>
		<view class="myUserInfo" v-if="token">
			<view class="user-info">
				
				<view class="user-name">{{userInfo.nickname}}</view>
				<view class="sign">{{userInfo.desc}}</view>
			</view>
			<view class="portrait">
				<image :src="userInfo.head_img"></image>
			</view>
		</view>
		
		<view @click="login" class="myUserInfo" v-if="!token">
			<view class="user-info">
				
				<view  class="user-name">未登录 点击此处登录</view>
				<view class="sign"></view>
			</view>
			<view class="portrait">
				<image :src="cdnUrl+'/touxiang.jpg'"></image>
			</view>
		</view>
		
		<view class="data-list">
			<view class="data-item">
				<view class="data-num">{{vipInfo.nums}}</view>
				<view class="data-text">总剩余次数</view>
			</view>
			<view class="data-item">
				<view class="data-num">{{vipInfo.free_nums}}</view>
				<view class="data-text">剩余当日次数</view>
			</view>
			<view class="data-item">
				<view class="data-num">{{vipInfo.day_nums}}</view>
				<view class="data-text">今日生成次数</view>
			</view>
			<view class="data-item">
				<view class="data-num">{{vipInfo.used_nums}}</view>
				<view class="data-text">总生成次数</view>
			</view>
		</view>
		
		<view v-if="vipInfo.show_vip" class="my-vip" @click="vip">
			<image :src="cdnUrl+'/vip.png'"></image>
		</view>
		
		<view class="my-item-top">
			<view class="item-title">我的常用</view>
			<view class="item-top-ret" @click="assistantList">
				<text>创作广场</text>
				<image :src="cdnUrl+'/more.png'"></image>
			</view>
			
		</view>
		
		<view v-if="myAssistant" class="my-item-list">
			<view v-if="myAssistant.length==0"><u-empty
			        mode="list" text="暂无"
			>
			</u-empty></view>
			<view @click="assistantInfo(value.id)" class="item-cart" v-for="(value,index) in myAssistant" :key="index">
				<image :src="value.icon"></image>
				<view class="cart-text">{{value.name}}</view>
			</view>
			
			<view style="clear: both;"></view>
		</view>
		
		<view class="my-item-top">
			<view class="item-title">其他服务</view>
			
		</view>
		
		<view class="my-item-list">
			<view  class="item-cart" >
				<button class="share-btn" open-type="contact">联系客服</button>
					<image :src="cdnUrl+'/kefu.png'"></image>
					<view class="cart-text">联系客服</view>
			</view>
		
			
			<view v-for="(val, index) in notice" :key="index"  class="item-cart" @click="noticeInfo(val.id)">
				<image :src="val.img"></image>
				<view class="cart-text">{{val.title}}</view>
			</view>
			
			<view v-if="token" class="item-cart" @click="history">
				<image :src="cdnUrl+'/ls.png'"></image>
				<view class="cart-text">创作历史</view>
			</view>
			
		
			<view v-if="token" class="item-cart" @click="loginout">
				<image :src="cdnUrl+'/xitong.png'"></image>
				<view class="cart-text">退出登录</view>
			</view>
			
			
			
			
			
					
			<view style="clear: both;"></view>
			<view class="copyright">
				<u-divider  text="本程序基于 chatAI-MP 搭建" @click="copyright"></u-divider>
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
				userInfo: [],
				token: '',
				
				myAssistant:[
					
				],
				msg:[],
				vipInfo:[],
				notice:[],
				cdnUrl:this.$config.cdnUrl
				
			}
		},
		onLoad(){
			uni.setNavigationBarTitle({
				title:uni.getStorageSync('project_title')
			})
			this.getMsg();
			this.getNoticeList();
			this.getMyAssistant();
			
		},
		onShow(){
			
			this.getVipInfo();
			this.loadUserInfo();
			
		},
		methods: {
			async getMsg(){
				var res = await this.$http.requestApi('GET', '/index/getMsg',{position:3});
				this.msg = res.data;
			},
			async getNoticeList(){
				var res = await this.$http.requestApi('GET', '/user/getNoticeList');
				this.notice = res.data;
			},
			async getMyAssistant(){
				var data = await this.$http.requestApi('GET', '/cate/myAssistant');
				this.myAssistant = data.data;
			},
			
			async getVipInfo(){
				var data = await this.$http.requestApi('GET', '/user/getVipInfo');
				this.vipInfo = data.data;
			},
			assistantInfo(id){
				uni.reLaunch({
					url: '/pages/create/index?assistant_id='+id,
				});
			},
			assistantList(){
				uni.navigateTo({
					url: '/pages/assistant/index'
				});
			},
			vip(){
				uni.navigateTo({
					 url: '/pages/my/buy'
				});
			},
			login(){
				uni.navigateTo({
					url: '/pages/my/login'
				});
			},
			history(){
				uni.navigateTo({
					url: '/pages/my/history'
				});
			},
			loginout(){
				uni.showModal({
					title: '提示',
					content: '确定要退出吗?',
					success(res) {
						if (res.confirm) {
							uni.removeStorageSync('token');
							uni.removeStorageSync('userInfo');
							uni.reLaunch({
								url: '/pages/my/my',
							});
						}
					}
				});
			},
			noticeInfo(id){
				uni.navigateTo({
					url: '/pages/my/notice?id='+id
				});
			},
			loadUserInfo(){
				this.userInfo = uni.getStorageSync('userInfo');
				this.token = uni.getStorageSync('token');
			},
			copyright(){
				uni.navigateToMiniProgram({
					appId: "wxb2b1a4e4b41291cc",
					path: "/pages/index/index",
					fail() {}
				})
			}
		}
	}
</script>

<style lang="less">
.myUserInfo{
	padding: 30rpx 40rpx;
	display: flex;
	align-items: center;
	justify-content: space-between;
	.user-name{
		color: #1A1A1A;
		font-size: 44rpx;
		font-weight: bold;
		margin-bottom: 5rpx;
	}
	.sign{
		color: #828899;
		font-size: 24rpx;
		margin-top: 5rpx;
	}
	.portrait{
		width: 120rpx;
		height: 120rpx;
		
		image{
			border-radius: 50%;
			width: 120rpx;
			height: 120rpx;
		}
	}
}
.data-list{
	display: flex;
	align-items: center;
	padding-top: 24rpx;
	padding-bottom: 34rpx;
	.data-item{
		width: 25%;
		text-align: center;
		.data-num{
			color: #242833;
			font-size: 34rpx;
			font-weight: bold;
		}
		.data-text{
			color: #828899;
			font-size: 22rpx;
		}
	}
}
.my-vip{
	margin: 0rpx 40rpx;
	text-align: center;
	box-shadow: 0rpx 15rpx 30rpx rgba(254, 64, 79, 0.2);
	border-radius: 20rpx;
	height: 122rpx;
	image{
		width: 670rpx;
		height: 122rpx;
	}
}
.my-item-top{
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 50rpx 40rpx 4rpx 40rpx;
	.item-title{
		color: #1A1A1A;
		font-size: 32rpx;
		font-weight: bold;
	}
	.item-top-ret{
		display: flex;
		align-items: center;
		text{
			color: #828899;
			font-size: 24rpx;
		}
		image{
			width: 24rpx;
			height: 24rpx;
		}
	}
}
.my-item-list{
	.item-cart{
		text-align: center;
		margin-top: 36rpx;
		float: left;
		width: 25%;
		.share-btn {
							width: 25%;
							text-align: center;
							opacity: 0;
							position: absolute;
							
						}
		.cart-text{
			color: #242833;
			font-size: 24rpx;
			margin-top: 10rpx;
		}
		image{
			width: 50rpx;
			height: 50rpx;
		}
	}
	.copyright{
		margin-top: 150rpx;
	}
}





</style>
