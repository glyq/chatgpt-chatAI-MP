import App from './App'

import request from './common/request.js';
Vue.prototype.$http = request;

import scoket from './common/webscoket.js';
Vue.prototype.$scoket = scoket;


import uView from "uview-ui"
Vue.use(uView);


import share from './util/wxShare/wxShare.js'
Vue.mixin(share)

import config from './common/config.js';
Vue.prototype.$config = config;

import wxpay from './util/wxpay/wxPayUtil.js';
Vue.prototype.$wxpay = wxpay;

// #ifndef VUE3
import Vue from 'vue'
import './uni.promisify.adaptor'
Vue.config.productionTip = false
App.mpType = 'app'
const app = new Vue({
  ...App
})
app.$mount()
// #endif

// #ifdef VUE3
import { createSSRApp } from 'vue'
export function createApp() {
  const app = createSSRApp(App)
  return {
    app
  }
}
// #endif