import axios from 'axios';
import md5 from "/utils/md5/md5.js";

axios.defaults.timeout = process.env.VUE_APP_TIMEOUT;

axios.defaults.headers["Content-Type"] = "application/json";
axios.defaults.headers["channel"] = process.env.VUE_APP_CHANNEL;


function requestEncrypt(data) {
    let secret = process.env.VUE_APP_SECRET;
    let _str = '';
    let sorted = {},
        keys = Object.keys(data);
    keys.sort();
    keys.forEach((key) => {
        _str += key + data[key];
    });
    _str = secret + _str + secret;
    return md5.hex_md5_32Upper(_str);
}

export default function request(options) {
    options.data.timestamp = (new Date()).valueOf();
    options.data.app_key = process.env.VUE_APP_KEY;
    options.data.sign = requestEncrypt(options.data);

    if(options.method == 'get'){
        var params = '';
        var keys = Object.keys(options.data);
        keys.forEach((key) => {
            params += key + '=' + options.data[key] + '&';
        });
        options.url += '?' + params;
    }

    var userInfo = localStorage.getItem("userInfo");
    var auth = '';
    if(userInfo){
        userInfo = JSON.parse(userInfo);
        auth = "Bearer "+userInfo.token;
    }

    axios.defaults.headers["AUTHORIZATION"] = auth;

    return new Promise((resolve, reject) => {
        axios(options).then(res => {
            resolve(res.data);
        }).catch(error => {
            reject(error);
        })
    })
}