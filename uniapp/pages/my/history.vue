<template>
	<view>
		<view class="uni-list-cell" hover-class="uni-list-cell-hover" v-for="(item, index) in lists" @click="info(item)">
		
			<view class="topTitleV">{{item.question}}</view>
			<view class="topTitleV unitV">{{item.anwser}}</view>
			
			<view
				style="display: flex; flex: 1; flex-wrap: wrap; margin-top: 0px; margin-left: -8px; height: 38px; width:calc(100vw-62px)">
				<view class="cellView" style="color: #41D380;backgroundColor: #ECFAF2">
					{{item.assistant}}
				</view>
		
		
			</view>
		
		
		</view>
		</view>


</template>

<script>
	export default {
		
		data() {
			return {
				lists : [
					
					
				]
			}
		},
		onLoad(){
			uni.setNavigationBarTitle({
				title:uni.getStorageSync('channel_info').name
			})
			this.getHistory();
			
			
		},
		methods: {
			async getHistory(){
				var data = await this.$http.requestApi('GET', '/chat/getCreation');
				this.lists = data.data;
			},
			info(item){
				var chatId = item.id;
				var assistant_name = item.assistant;
				uni.navigateTo({
					url: '/pages/chat/chat?chatId='+chatId+'&stream=1'+'&assistant='+this.assistant_name
				});
			}
		}
	}
</script>

<style scoped>
	.uni-list-cell {
		flex-direction: column;
		margin-top: 10px;
		background-color: #FAFAFA;
		padding: 6px 12px;
	
	
	}
	
	.topTitleV {
	
		height: 26px;
		line-height: 26px;
		color: #333333;
		font-weight: 500;
		font-size: 14px;
		
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	
	}
	.unitV{
		color: #555555;
		font-size: 12px;
		margin-top: 0px;
		
	}
	
	.cellView {
		margin-top: 8px;
		margin-left: 8px;
		height: 22px;
		line-height: 22px;
		text-align: center;
		border-radius: 2px;
		padding: 0px 4px !important;
		font-size: 12px;
	
		color: #4272FF;
		background: #F3F4F6;
	}
	
</style>