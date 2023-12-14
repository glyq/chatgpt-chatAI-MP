
let socket = "";
let global_callback = null;
let weburl = process.env.VUE_APP_WEBSOCKET_URL;

export const sendWebsocket = function(agentData, callback) {
    global_callback = callback;
    socketOnSend(agentData);
}


export const closeWebsocket = function() {
    if (socket) {
        socket.close();
        socket = '';
    }
}

export const initWebSocket = function(e) {
    if (!window.WebSocket) {
        return;
    }
    if (!socket) {
        socket = new WebSocket(weburl);
        socketOnOpen();
        socketOnError();
        socketOnMessage(e);
        socketOnClose(e);
    }
    return socket;
}



function socketOnOpen() {
    socket.onopen = () => {
    };
}

function socketOnClose(e) {
    socket.onclose = () => {
        socket = '';
        e.creating = false;
    };
}



function socketOnSend(data) {

    var userInfo = localStorage.getItem("userInfo");
    var auth = '';
    if(userInfo){
        userInfo = JSON.parse(userInfo);
        auth = userInfo.token;
    }

    data.token = auth;

    var str = '';
    var keys = Object.keys(data);

    keys.forEach((key) => {
        str += key +'='+ data[key]+"&"
    });
    var num = 1;
    var interval = setInterval(() => {

        if(socket.readyState == 1){
            socket.send(encodeURIComponent(str));
            clearInterval(interval);
        }

        if((num >= 5)){
            clearInterval(interval);
        }
        num++;
    }, 1000);

}

function socketOnError() {
    socket.onerror = () => {
        console.log("socket 链接失败");
    };
}

function socketOnMessage(that) {
    socket.onmessage = (e) => {
        global_callback(e.data);
    };
}