<template>
  <div id="app">
    <main class="main">
      <div class="div_flex">
        <div class="div_h">
          <div ref="chat" class="scroll">
            <div class="div_center">
              <div class="chat_list">
                <div></div>
                <template v-for="v, key in session">
                  <div v-if="v.anwser" class="anwser"
                       style="padding-bottom: 6px;padding-left: 82px;padding-right: 82px;">
                    <div class="chat_item">
                      <div class="headimg headimg_item"><img :src="assistant_img" alt="头像"></div>
                      <div class="anwser_box">
                        <div class="anwser_box_item">
                          <div class="anwser_info">
                            <div>
                              <div>
                                <div></div>
                                <div>
                                  <div v-if="key == 0" v-html="markdown(v.anwser)"
                                       :class="{ 'result-streaming': creating }" class="markdown anwser_input"></div>
                                  <div v-else v-html="markdown(v.anwser)" class="markdown anwser_input"></div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="chat_rate">
                          <el-rate v-if="!creating && key != session.length-1" v-model="v.rate" show-text
                                   @change="chatRate(v.rate,v.id)"></el-rate>
                          <div class="anwser_bot">
                            <div class="anwser_bot_item">
                                <span class="anwser_copy">
                                <el-tooltip class="item" effect="dark" content="复制" placement="bottom">
                                  <i v-if="!creating && key != session.length-1" class="el-icon-document-copy"
                                     @click="copy(v.anwser)"></i>
                                </el-tooltip>
                                  <i v-if="creating && key == 0" class="el-icon-loading"></i>
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-if="v.question" class="anwser"
                       style="padding-bottom: 6px; padding-left: 82px; padding-right: 82px;">
                    <div class="chat_item">
                      <div class="headimg"><img :src="userInfo.head_img" alt="头像"></div>
                      <div class="question_div">
                        <div class="question_info"><span>{{ v.question }}</span></div>
                      </div>
                    </div>
                  </div>
                </template>
              </div>

              <el-drawer title="" :visible.sync="drawer" :with-header="false" direction="ltr" size="50%">
                <div class="welcome_window" style="margin-left:20px;margin-top:20px">
                  <div v-for="cate, cateIndex in assistantList" class="welcome_list">
                    <div class="welcome_content">
                      <div class="welcome_title">
                        <div class="welcome_label">{{ cate.label }}</div>
                      </div>
                      <div class="welcome_card">
                        <div v-for="assistant, assistantIndex in cate.children" class="welcome_card_item"
                             style="display:flex;margin-right:10px" @click="assistantClick(assistant.id)">
                          <el-image style="width: 50px; height: 50px; float:left" :src="assistant.icon"
                                    fit="cover"></el-image>
                          <div style="float:left;margin-left:30px">
                            <div class="welcome_item_title">
                              <div class="welcome_item_label">{{ assistant.label }}</div>
                            </div>
                            <div class="welcome_item_desc">
                              {{ assistant.desc }}
                            </div>
                          </div>
                        </div>
                        <div class="welcome_empty"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </el-drawer>

              <div v-if="session.length == 0" class="welcome_window">
                <div class="anwser" style="padding-bottom: 6px; padding-right: 82px;margin-bottom: 30px">
                  <div class="chat_item" style="max-width:950px">
                    <div class="anwser_box">
                      <div class="anwser_box_item">
                        <div class="anwser_info">
                          <div>
                            <div>
                              <div></div>
                              <div>
                                <div v-html="welcome"
                                     class="markdown  anwser_input"></div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>


                <div v-for="cate, cateIndex in assistantList" class="welcome_list">
                  <div class="welcome_content">
                    <div class="welcome_title">
                      <div class="welcome_label">{{ cate.label }}</div>
                    </div>
                    <div class="welcome_card">
                      <div v-for="assistant, assistantIndex in cate.children" class="welcome_card_item"
                           style="display:flex;margin-right:10px" @click="assistantClick(assistant.id)">
                        <el-image style="width: 50px; height: 50px; float:left" :src="assistant.icon"
                                  fit="cover"></el-image>
                        <div style="float:left;margin-left:30px">
                          <div class="welcome_item_title">
                            <div class="welcome_item_label">{{ assistant.label }}</div>
                          </div>
                          <div class="welcome_item_desc">
                            {{ assistant.desc }}
                          </div>
                        </div>
                      </div>
                      <div class="welcome_empty"></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="anwser_foot"></div>
            </div>

            <transition name="el-fade-in-linear">
              <div v-show="isShowBottom" @click="handleScrollBottom" class="handle_bottom">
                <i style="margin: auto" class="el-icon-bottom"></i>
              </div>
            </transition>
          </div>
        </div>
      </div>

      <div>
        <form v-if="session.length > 0" class="session_form">
          <div class="session_input_list">
            <div class="session_stop">
              <el-button style="background-color: #4c75f6" @click="stop" v-if="creating && stream == 2" type="primary"
                         icon="el-icon-video-pause">
                <el-icon>
                  <VideoPause/>
                </el-icon>
                停止输出
              </el-button>
            </div>
            <div v-for="input, key in inputItem">
              <div v-if="input.type == 'input'" style="width: 60%; float: left;margin:0 2% 10px 0; display: flex;"
                   class="session_input">
                <div class="session_input_title"><p>{{ input.title }}</p></div>
                <div style="flex:1">
                  <el-input type="textarea" :placeholder="input.default" v-model="input.val" :show-word-limit="true"
                            rows="1" :maxlength="20" :disabled="creating" @keydown.enter.native.prevent=""></el-input>
                </div>
              </div>
              <div v-if="input.type == 'slider'" style="width: 36%; float: left;margin:0 2% 10px 0; display: flex;"
                   class="session_input">
                <div class="session_input_title"><p>{{ input.title }}</p></div>
                <div style="flex:1">
                  <el-input type="textarea" :placeholder="input.default" v-model.number="input.val" rows="1"
                            :disabled="creating" @keydown.enter.native.prevent=""
                            @input="inputNum(inputItem,key)"></el-input>
                </div>
              </div>
            </div>

            <div v-for="input, key in inputItem">
              <div v-if="input.type == 'textarea'" class="session_input" style="display: flex">
                <div class="session_input_title"><p>{{ input.title }}</p></div>
                <div style="flex:0.95">
                  <el-input type="textarea" :placeholder="input.default" v-model="input.val" :show-word-limit="true"
                            :autosize="{ minRows: 2, maxRows: 10 }" rows="2" :maxlength="400"
                            @keydown.enter.native.prevent="inputEnter" :disabled="creating"
                            @input="inputChangeEnter(inputItem,key)"></el-input>
                </div>
                <div class="session_send">
                  <el-button type="primary" style="background-color: #4c75f6;" @click.stop.prevent="send" size="mini"
                             :disabled="creating" :loading="creating" icon="el-icon-s-promotion" circle></el-button>
                </div>
              </div>
            </div>
          </div>
        </form>
        <div class="footer_info">
          <span v-if="session.length > 0" style="color: black;font-size: 15px;">流式输出
            <span style="margin-left: 10px">
              <el-switch v-model="streamDefault" active-color="#4c75f6" inactive-color="#ff4949" @change="streamChange"
                         :disabled="creating"></el-switch>
            </span>
          </span>
          <span style="margin-left: 10px">所有内容均由人工智能模型生成，生成内容不代表我们的观点和立场。</span>
          <a style="margin-left: 10px" href="https://github.com/qianxuanxi/chatAI-MP" target="_blank"
             class="underline">本网站基于 chatAI-MP 搭建</a>
        </div>
      </div>
    </main>

    <div class="menu">
      <div class="menu_list">
        <div class="scrollbar-trigger menu_info">

          <nav class="menu_nav">
            <el-button style="background-color: #4c75f6;margin-bottom: 40px;margin-top: 10px" @click="drawer = true"
                       type="info" icon="el-icon-plus">新建对话
            </el-button>

            <div class="menu_session_list" style="padding-bottom: 5px;">
              <div class="menu_sessions">
                <template v-for="session, key in sessions">
                  <div v-if="session.editing" class="session_edit_list">
                    <i class="el-icon-chat-square"></i>
                    <input id="sessionTitle" v-model="sessionNameEdit" @blur="cancelEditSession(key,session)"
                           type="text" class="session_edit_input" autofocus="true">
                    <div class="edit_options">
                      <button @click="saveEditSession(key, session)" class="edit_button">
                        <i class="el-icon-check"></i>
                      </button>
                      <button @click="cancelEditSession(key,session)" class="edit_button">
                        <i class="el-icon-close"></i>
                      </button>
                    </div>
                  </div>


                  <a v-else @click.stop.prevent="selectSession(session)"
                     :class="{ 'session_selected': session.selected, 'session_unselected': !session.selected }"
                     class="session_box">
                    <i class="el-icon-chat-dot-square"></i>
                    <div style="margin-left: 5px"
                         class="session_name">
                      {{ session.name }}
                    </div>
                    <div v-show="session.selected" class="edit_options">
                      <button @click="editSession(key,session)" class="edit_button">
                        <i class="el-icon-edit-outline"></i>
                      </button>
                      <button @click.stop.prevent="deleteSessionById(key,session.id)" class="edit_button">
                          <i slot="reference" class="el-icon-delete"></i>
                      </button>
                    </div>
                  </a>
                </template>
              </div>
            </div>

            <div v-if="sessions.length > 0" @click.stop.prevent="deleteSessions()" class="sessions_option">
              <i class="el-icon-delete-solid" style="margin-right: 5px"></i>
              清除所有对话
            </div>

            <div v-if="userInfo.nickname" class="sessions_option">
              <span style="width:70px;">剩余次数</span>
              <span style="width:50px;margin-left:5px">{{ userNums.nums }}</span>
              <el-button v-if="userNums.show_vip > 0" @click="showAddMsg(1)" style="background-color: #4c75f6"
                         type="primary">获取更多
              </el-button>
            </div>

            <div v-if="userInfo.nickname" class="sessions_option">
              <el-avatar style="width: 30px;height: 30px" :src="userInfo.head_img"></el-avatar>
              <span style="width:90px;margin-left:5px">{{ userInfo.nickname }}</span>
              <el-button @click="loginout" style="background-color: #4c75f6" type="primary">退出登录</el-button>
            </div>
            <div v-else class="sessions_option">
              <el-button @click="login" style="background-color: #4c75f6" type="info">登录</el-button>
            </div>
          </nav>
        </div>
      </div>
    </div>

    <el-dialog @open="loginOpen" @closed="loginClose" custom-class="dialogClass" width="30%" title="微信登录"
               :visible.sync="dialogVisible">
      <img v-if="loginImg" @click="loginOpen" class="login_img" :src="loginImg" alt="登录">
      <div v-else class="login_loading">
        <i class="el-icon-loading"></i>
      </div>
      <div class="login_msg">
        <span>{{ loginMsg }}</span>
      </div>
    </el-dialog>


    <el-dialog custom-class="dialogClass" width="30%" :title="addMsg" :visible.sync="addNum">
      <img v-if="addImg" class="login_img" :src="addImg">
      <div v-else class="login_loading">
        <i class="el-icon-loading"></i>
      </div>
      <div class="login_msg">
        <span>微信扫描二维码可以免费获取次数哦</span>
      </div>

    </el-dialog>

  </div>
</template>

<script>

import {marked} from 'marked';
import hljs from "highlight.js";
import 'highlight.js/styles/github.css';

import request from '../utils/request/request';
import {initWebSocket, closeWebsocket, sendWebsocket} from "../utils/websocket/websocket";


const renderer = {
  code(code, infostring) {
    var codeHtml = code
    if (infostring && infostring == "html") {
      codeHtml = encodeURIComponent(code);
    }
    if (infostring) {
      codeHtml = hljs.highlightAuto(code).value
    }

    return `<div class="md_head">
      <div class="code_header">
        <span>${infostring || ""}</span>
      </div>
      <div class="md_foot">
        <code class="markdown_code">${codeHtml}</code>
      </div>
    </div>`;
  }

};
marked.use({renderer});

export default {
  data() {
    return {
      sessions: [],
      session: [],
      creating: false,
      isShowBottom: false,
      selectedSession: undefined,
      sessionNameEdit: "",
      inputItem: [],
      assistant: 0,
      chatId: 0,
      stream: 0,
      assistantList: [],
      drawer: false,
      streamDefault: true,
      streamShow: 0,
      assistant_img: '',
      assistant_name: '',
      assistant_desc: '',
      welcome: '',
      title: '',
      userInfo: [],
      dialogVisible: false,
      loginImg: '',
      loginMsg: '',
      scan: false,
      addNum: false,
      addImg: '',
      addMsg: '',
      userNums: 0

    };
  },
  methods: {
    inputNum(input, key) {
      if (!input[key].val) {
        return;
      }
      var num = parseFloat(input[key].val);
      if (isNaN(num)) {
        input[key].val = '';
        return;
      }
      input[key].val = num;
      if (num > 1000) {
        input[key].val = 1000;
      }

    },

    inputChangeEnter(input, key) {
      input[key].val = input[key].val.replace(/ /g, "\n")
    },


    handleScrollBottom() {
      this.$nextTick(() => {
        let scrollElem = this.$refs.chat;
        scrollElem.scrollTo({top: scrollElem.scrollHeight, behavior: 'smooth'});
      });
    },

    getSession() {
      var that = this;
      request({
        url: '/api/Chat/getSession',
        data: {},
        method: 'get'
      }).then(res => {
        if (res.code == 0) {
          that.sessions = res.data;
        }

        if (res.code == 1003) {
          that.dialogVisible = true;
        }
      })
    },

    getVipInfo() {
      var that = this;
      request({
        url: '/api/user/getVipInfo',
        data: {},
        method: 'post'
      }).then(res => {
        if (res.code == 0) {
          that.userNums = res.data;
        }
        if (res.code == 1003) {
          that.dialogVisible = true;
        }
      })
    },

    markdown(md) {
      if (md == "") {
        return "<p></p>"
      }
      md = this.markdownChange(md, "```")

      var htmlMD = marked.parse(md);
      htmlMD = htmlMD.trim();
      return htmlMD;
    },

    markdownChange(str, substr) {
      const matches = str.match(new RegExp(substr, 'g'));
      const count = matches ? matches.length : 0;
      const isOdd = count % 2 === 1;
      return isOdd ? str + "\n" + substr : str;
    },

    selectSession(session) {
      if (this.checkCreating()) {
        return;
      }
      var that = this;
      if (this.selectedSession) {
        if (this.selectedSession.id == session.id) {
          return;
        }
        this.selectedSession.selected = false;
      }
      session.selected = true;
      this.assistant = session.assistant_id
      this.selectedSession = session;
      request({
        url: '/api/cate/assistantInfo',
        data: {assistant_id: session.assistant_id},
        method: 'get'
      }).then(res => {
        if (res.code == 0) {
          that.inputItem = res.data.keywords;
          that.assistant_img = res.data.icon;
          that.assistant_desc = res.data.desc;
          that.assistant_name = res.data.name;
          that.streamShow = res.data.stream.show;
          var stream = localStorage.getItem("stream");
          if (stream == 2) {
            that.streamDefault = true;
          } else if (stream == 1) {
            that.streamDefault = false;
          } else {
            that.streamDefault = res.data.stream.default;
          }
          that.streamChange(that.streamDefault);
        }

        request({
          url: '/api/Chat/getSessionInfo',
          data: {id: session.id},
          method: 'get'
        }).then(res => {
          if (res.code == 1003) {
            that.dialogVisible = true;
            return;
          }
          if (res.code == 0) {
            that.session = res.data;

            var html = '<p style="font-size: 22px; font-weight: 500; color: black;">欢迎使用' + this.title +
                '</p><p style="font-size: 14px; margin-top: 10px;">当前选择的创作模版为：' +
                that.assistant_name + '</p><p style="font-size: 12px; margin-top: 10px;color:#8e8ea0;">' +
                that.assistant_desc + '</p>'

            that.session.push({
              'anwser': html
            });
            setTimeout(() => {
              that.handleScrollBottom();
            }, 300)
          } else {
            var msg = res.message ? res.message : '未知错误，请稍后重试！';
            that.$message.error(msg);
          }
        })
      })
    },

    inputEnter(e) {
      if (!e.shiftKey && e.keyCode == 13) {
        e.cancelBubble = true;
        e.stopPropagation();
        e.preventDefault();
        this.send();
      }
    },

    send() {
      if (this.checkCreating()) {
        return;
      }
      var question = '';
      for (var i = 0; i < this.inputItem.length; i++) {
        question += this.inputItem[i].title + ': ' + this.inputItem[i].val + "\n";
        if (this.inputItem[i].require && !this.inputItem[i].val) {
          this.$message.error("请输入" + this.inputItem[i].title);
          return;
        }
      }

      this.creating = true;
      this.chatId = 0;

      this.session.unshift({
        "question": question,
        "anwser": '内容生成中，请耐心等待...'
      })

      this.handleScrollBottom();

      var post = {
        assistant_id: this.assistant,
        input: JSON.stringify(this.inputItem),
        platform: 'pc'
      }

      if (this.streamShow) {
        post.stream = this.stream
      }

      var that = this;

      request({
        url: '/api/chat/create',
        data: post,
        method: 'post'
      }).then(res => {
        if (res.code == 1003) {
          that.dialogVisible = true;
          that.creating = false;
          that.session.splice(0, 1);
          return;
        }
        if (res.code == 2009) {
          that.showAddMsg();
          that.creating = false;
          that.session.splice(0, 1);
          return;
        }

        if (res.code == 0 && res.data.chat_id) {
          if (that.stream == 1) {
            request({
              url: '/api/chat/getChat',
              data: {
                chatId: res.data.chat_id,
              },
              method: 'post'
            }).then(result => {
              if (result.code == 0 && result.data.msg) {
                that.session[0].anwser = result.data.msg
                that.session[0].id = res.data.chat_id
                that.creating = false;
                that.handleScrollBottom();
                if (that.userNums.nums != 0) {
                  that.userNums.nums -= 1;
                }
              } else {
                that.$message.error(" 生成失败，请稍后重试！");
                that.creating = false;
                that.session.splice(0, 1);
              }
            })
          }

          if (that.stream == 2) {
            var socket = initWebSocket(that);
            var post = {};
            post.chatId = res.data.chat_id;
            if (that.userNums.nums != 0) {
              that.userNums.nums -= 1;
            }
            sendWebsocket(post, function (e) {
              var json = JSON.parse(e);

              if (json.code != 0) {
                var msg = json.message ? json.message : '未知错误,请重试';
                that.$message.error(msg);
                that.creating = false;
                that.session.splice(0, 1);
                return;
              }
              that.session[0].anwser = json.data
              that.session[0].id = res.data.chat_id
              that.creating = true;
              that.handleScrollBottom();
            });
          }
        } else {
          var msg = res.message ? res.message : '未知错误';
          that.$message.error(msg);
          that.creating = false;
          that.session.splice(0, 1);
        }
      })
    },

    stop() {
      closeWebsocket();
      this.creating = false;
    },

    newSession(id) {
      if (this.checkCreating()) {
        return;
      }
      var that = this;
      var newSession = {
        "assistant_id": id,
        "desc": '',
        "id": '',
        "name": '',
        "selected": true,
        "edit": true,
      }
      request({
        url: '/api/chat/createSession',
        data: {
          assistant_id: id
        },
        method: 'post'
      }).then(result => {
        if (result.code == 1003) {
          that.dialogVisible = true;
          return;
        }
        if (result.code == 0 && result.data) {
          newSession.id = result.data.id;
          newSession.name = result.data.assistant;
          that.sessions.unshift(newSession)
          that.selectSession(newSession);
        } else {
          var msg = res.message ? res.message : '未知错误，请稍后重试！';
          that.$message.error(msg);
        }
      })
    },

    getAssistantList() {
      request({
        url: '/api/cate/cateAssistant',
        data: {},
        method: 'get'
      }).then(result => {
        if (result.code == 0 && result.data) {
          this.assistantList = result.data;
        }
      })
    },

    assistantClick(id) {
      this.drawer = false;
      for (var i = 0; i < this.sessions.length; i++) {
        if (this.sessions[i].assistant_id == id) {
          this.selectSession(this.sessions[i]);
          return;
        }
      }
      this.newSession(id);
    },

    editSession(key, session) {
      if (this.checkCreating()) {
        return;
      }
      this.sessionNameEdit = session.name;
      session.editing = true;
      this.$set(this.sessions, key, session);
      setTimeout(() => {
        document.getElementById("sessionTitle").focus();
      }, 300)
    },

    cancelEditSession(key, session) {
      setTimeout(() => {
        session.editing = false;
        this.$set(this.sessions, key, session);
      }, 100);
    },

    saveEditSession(key, session) {
      var that = this;
      session.editing = false;
      if (session.name == this.sessionNameEdit) {
        return;
      }
      request({
        url: '/api/chat/saveSession',
        data: {
          id: session.id,
          title: this.sessionNameEdit
        },
        method: 'post'
      }).then(result => {
        if (result.code == 1003) {
          that.dialogVisible = true;
          return;
        }
        if (result.code == 0) {
          session.name = result.data.title;
          that.$set(this.sessions, key, session);
          that.$message({
            message: '修改成功',
            type: 'success'
          });
        } else {
          that.$message.error('修改失败');
          that.$set(this.sessions, key, session);
        }
      })
    },

    deleteSession(key, id = 0) {
      if (this.checkCreating()) {
        return;
      }
      var that = this;
      request({
        url: '/api/chat/deleteSession',
        data: {
          id: id,
        },
        method: 'post'
      }).then(result => {
        if (result.code == 1003) {
          that.dialogVisible = true;
          return;
        }
        if (result.code == 0) {
          that.session = [];
          if (id) {
            that.sessions.splice(key, 1);
          } else {
            that.sessions = [];
          }
          that.$message({
            message: '删除成功',
            type: 'success'
          });
        } else {
          that.$message.error('删除失败');
        }
      })
    },

    deleteSessions() {
      if (this.checkCreating()) {
        return;
      }
      this.$confirm('此操作将清除所有对话, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        confirmButtonClass: 'confirmClass',
        type: 'warning'
      }).then(() => {
        this.deleteSession(0);
      }).catch(() => {
      });
    },

    deleteSessionById(key, id = 0) {
      if (this.checkCreating()) {
        return;
      }
      this.$confirm('此操作将清除此对话, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        confirmButtonClass: 'confirmClass',
        type: 'warning'
      }).then(() => {
        this.deleteSession(key, id);
      }).catch(() => {
      });
    },

    copy(msg) {
      this.$copyText(msg)
          .then((res) => {
            this.$message.success("复制成功!");
          })
          .catch((err) => {
            this.$message.error("该浏览器不支持自动复制, 请手动复制");
          });
    },

    streamChange(e) {
      if (e) {
        localStorage.setItem("stream", 2);
        this.stream = 2;
      } else {
        localStorage.setItem("stream", 1);
        this.stream = 1;
      }
    },
    checkCreating() {
      if (this.creating) {
        this.$message({
          message: '正在生成内容哦，请稍后再试！',
          type: 'warning'
        });
        return true;
      }
      return false;
    },

    showBotton() {
      let chatDiv = this.$refs.chat;
      if (!chatDiv) {
        return;
      }
      if (chatDiv.scrollHeight <= chatDiv.clientHeight) {
        this.isShowBottom = false;
        return;
      }
      const scrollTop = chatDiv.scrollTop;
      const windowHeight = chatDiv.clientHeight;
      const scrollHeight = chatDiv.scrollHeight;
      if (scrollTop + windowHeight >= scrollHeight - 50) {
        this.isShowBottom = false;
        return;
      }
      this.isShowBottom = true;
    },

    chatRate(rate, id) {
      var that = this;
      request({
        url: '/api/chat/score',
        data: {
          id: id,
          score: rate,
        },
        method: 'post'
      }).then(result => {
        if (result.code == 1003) {
          that.dialogVisible = true;
        }
      })
    },

    init() {
      var userInfo = localStorage.getItem("userInfo");
      if (userInfo) {
        this.userInfo = JSON.parse(userInfo);
      }
      var that = this;
      request({
        url: '/api/index/getInfo',
        data: {},
        method: 'get'
      }).then(result => {
        var data = result.data;
        var html = '<p style="font-size: 22px; font-weight: 500; color: black;">你好呀，我是' + data.name +
            '</p><p style="font-size: 14px; margin-top: 10px;">' +
            data.desc + '</p><p style="font-size: 12px; margin-top: 10px;color:#8e8ea0;">选择下面的模板开始使用吧</p>';
        this.welcome = html;
        this.title = data.name;
      })
      this.getSession();
      this.getAssistantList();
      this.getVipInfo();
    },

    login() {
      this.dialogVisible = true;
    },

    loginClose() {
      this.scan = false;
    },

    loginOpen() {
      if (this.scan) {
        return;
      }
      this.loginImg = '';
      this.loginMsg = '请使用微信扫一扫后一键登录';
      var that = this;
      request({
        url: '/api/user/getQrCode',
        data: {},
        method: 'post',
      }).then(response => {
        this.loginImg = response.data.img
        this.scan = true;
        var scene = response.data.scene;
        var socket = new WebSocket(process.env.VUE_APP_LOGIN_URL);
        socket.onclose = () => {
          this.loginImg = process.env.VUE_APP_CDN_URL+'/fresh.png';
          this.loginMsg = '二维码已失效，请点击图片刷新';
          this.scan = false;
        };
        socket.onmessage = (e) => {
          var data = JSON.parse(e.data);
          if (data.status == 1) {
            //登录成功
            var userInfo = data.userInfo;
            localStorage.setItem("userInfo", JSON.stringify(userInfo));

            that.dialogVisible = false;
            that.scan = false;
            socket.close();
            this.init();
          }
        };
        socket.onopen = () => {
          socket.send(encodeURIComponent(scene));
        };
      })
    },

    loginout() {
      if (this.checkCreating()) {
        return;
      }
      this.userInfo = [];
      localStorage.setItem("userInfo", []);
      this.session = [];
      this.sessions = [];
      this.sessions = [];
    },

    showAddMsg(type) {
      this.addMsg = '您的可用生成次数不足';
      if (type == 1) {
        this.addMsg = '获取更多生成次数';
      }
      var that = this;
      request({
        url: '/api/user/getQrCode',
        data: {
          type: 1
        },
        method: 'post'
      }).then(result => {
        that.addImg = result.data.img;
        that.addNum = true;
      })
    }
  },


  mounted: function () {
    document.title = process.env.VUE_APP_NAME;
    this.init();
    let chatDiv = this.$refs.chat;
    chatDiv.addEventListener('scroll', this.showBotton, true);
  }
};
</script>


<style lang="scss">

html,
body {
  height: 100%;
  width: 100%;
}

#app {
  height: 100%;
}


.scroll {
  height: 100%;
  overflow-y: auto;
  width: 100%;
}

.code_header {
  border-top-left-radius: 5px;
  border-top-right-radius: 5px;
  display: flex;
  align-items: center;
  position: relative;
  color: rgba(217, 217, 227, 1);
  padding-left: 1rem;
  padding-right: 1rem;
  padding-bottom: .5rem;
  padding-top: .5rem;
  font-size: .75rem;
  line-height: 1rem;
  font-family: Söhne, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif, Helvetica Neue, Arial, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji

}


.confirmClass {
  background-color: #4c75f6 !important;
}

.dialogClass {
  width: 400px !important;
  height: 400px !important;
}


.el-textarea__inner {
  resize: none !important;
  border-width: 0 !important;
  margin-right: 20px !important;
  width: 100% !important;
  background-color: transparent !important;
  padding: 0 !important;
  padding-left: .5rem !important;
  padding-right: 1.75rem !important;
}


.main {
  position: relative;
  height: 100%;
  width: 100%;
  transition-duration: .15s;
  transition-property: width;
  transition-timing-function: cubic-bezier(.4, 0, .2, 1);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  align-items: stretch;
  flex: 1 1 0%;
  background: hsla(0, 0%, 100%, .86);
}

.div_flex {
  flex: 1 1 0%;
  overflow: hidden;
}

.div_h {
  height: 100%;
}

.div_center {
  display: flex;
  flex-direction: column;
  align-items: center;
  font-size: .875rem;
  line-height: 1.25rem;
}

.chat_list {
  display: flex;
  flex-direction: column-reverse;
  justify-content: flex-end;
  margin: auto
}

.anwser {
  align-items: center;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 6px 0;
  position: relative;
}

.anwser.l2kakEF9 {
  background: rgba(219, 222, 240, .4)
}

.anwser .chat_item {
  max-width: 800px;
  min-width: 800px;
  position: relative;
  width: 100%
}

.headimg {
  align-items: center;
  border-radius: 5px;
  display: flex;
  justify-content: center;
  overflow: hidden;
  -webkit-user-select: none;
  -moz-user-select: none;
  user-select: none
}

@media screen and (min-width: 801px) {
  .headimg {
    height: 26px;
    left: -36px;
    position: absolute;
    top: 4px;
    width: 26px
  }

  .headimg_item {
    top: 0
  }
}

@media screen and (max-width: 800px) {
  .headimg {
    flex: none;
    height: .28rem;
    width: .28rem
  }

}

.anwser_box {
  cursor: default
}

.anwser_box .anwser_box_item {
  background: #f5faff;
  border-radius: 8px;
  box-shadow: 0 16px 20px 0 rgba(174, 167, 223, .06);
  display: flex;
  flex-direction: column;
  min-height: 64px;
  padding: 20px 28px;
  position: relative
}

.anwser_box .anwser_box_item .anwser_info {
  overflow-x: auto;
  position: relative
}


.markdown ol {
  counter-reset: item
}

.markdown ul li {
  display: block;
  margin: 0;
  position: relative
}

.markdown ul li:before {
  content: "•";
  font-size: .875rem;
  line-height: 1.25rem;
  margin-left: -1rem;
  position: absolute
}

.markdown {
  max-width: none
}

.markdown h1, .markdown h2 {
  font-weight: 600
}

.markdown h2 {
  margin-bottom: 1rem;
  margin-top: 2rem
}

.markdown h3 {
  font-weight: 600
}

.markdown h3, .markdown h4 {
  margin-bottom: .5rem;
  margin-top: 1rem
}

.markdown h4 {
  font-weight: 400
}

.markdown h5 {
  font-weight: 600
}

.markdown blockquote {
  border-color: rgba(142, 142, 160, 1);
  border-left-width: 2px;
  line-height: 1rem;
  padding-left: 1rem
}

.markdown ol, .markdown ul {
  display: flex;
  flex-direction: column;
  padding-left: 1rem
}

.markdown ol li, .markdown ol li > p, .markdown ol ol, .markdown ol ul, .markdown ul li, .markdown ul li > p, .markdown ul ol, .markdown ul ul {
  margin: 0
}

.markdown table {
  border-collapse: separate;
  border-spacing: 0px 0px;
  width: 100%
}

.markdown th {
  background-color: rgba(236, 236, 241, .2);
  border-bottom-width: 1px;
  border-left-width: 1px;
  border-top-width: 1px;
  padding: .25rem .75rem
}

.markdown th:first-child {
  border-top-left-radius: .375rem
}

.markdown th:last-child {
  border-right-width: 1px;
  border-top-right-radius: .375rem
}

.markdown td {
  border-bottom-width: 1px;
  border-left-width: 1px;
  padding: .25rem .75rem
}

.markdown td:last-child {
  border-right-width: 1px
}

.markdown tbody tr:last-child td:first-child {
  border-bottom-left-radius: .375rem
}

.markdown tbody tr:last-child td:last-child {
  border-bottom-right-radius: .375rem
}

.markdown a {
  text-decoration-line: underline;
  text-underline-offset: 2px
}

.anwser_input {
  width: 100%;
  word-wrap: break-word;
  font-size: 1rem;
  line-height: 1.75;
}

.chat_rate {
  display: flex;
  height: 20px;
  margin-top: 8px;
  padding: 0 10px 0 16px
}

.anwser_bot {
  display: flex;
  flex: 1;
  justify-content: flex-end;
  position: relative
}

.anwser_bot_item {
  align-items: center;
  display: flex;
  margin-top: -2px
}

.anwser_copy {
  align-items: center;
  cursor: pointer;
  display: flex;
  height: 23px;
  justify-content: center;
  position: relative;
  width: 23px
}

.question_div {
  padding: 7px 0 13px;
  position: relative
}

.question_div .question_info {
  color: #05073b;
  font-size: 16px;
  font-weight: 500;
  line-height: 24px;
  overflow-x: auto;
  white-space: pre-wrap;
  word-break: break-all
}

.result-streaming > :not(ol):not(ul):not(pre):last-child:after, .result-streaming > ol:last-child li:last-child:after, .result-streaming > pre:last-child code:after, .result-streaming > ul:last-child li:last-child:after {
  -webkit-animation: blink 1s steps(5, start) infinite;
  animation: blink 1s steps(5, start) infinite;
  content: "▋";
  margin-left: .25rem;
  vertical-align: baseline
}

@media screen and (max-width: 1601px) {

  .welcome_window .welcome_list {
    border-radius: 8px;
    color: #43436b;
    display: flex;
    font-size: 15px;
    height: auto;
    margin: 0 auto;
    min-height: 43px
  }

  .welcome_window .welcome_list .welcome_content {
    background: #d8dde7;
    border-radius: 8px;
    box-sizing: border-box;
    margin: 0 auto 56px;
    padding: 16.5px 16.5px 4px 29px;
    width: 950px
  }

  .welcome_window .welcome_list .welcome_title {
    align-items: center;
    color: #43436b;
    display: flex;
    font-size: 18px;
    justify-content: space-between;
    margin-bottom: 14px
  }

  .welcome_window .welcome_list .welcome_title .welcome_label > span {
    color: #b7b8cc;
    font-size: 12px;
    margin-left: 16px
  }


  .welcome_window .welcome_list .welcome_title img {
    height: auto;
    margin-right: 8px;
    width: 18px
  }


  .welcome_window .welcome_card {
    color: #43436b;
    display: flex;
    flex-wrap: wrap;
    height: auto;
    justify-content: left;
    overflow-y: auto;
    padding-top: 1px;
    scrollbar-color: #dde7f7 transparent;
    scrollbar-width: thin;
    width: 100%
  }

  .welcome_window .welcome_card::-webkit-scrollbar {
    height: 0;
    width: 6px
  }

  .welcome_window .welcome_card::-webkit-scrollbar-thumb {
    background: #dde7f7;
    border-radius: 6px;
    -webkit-box-shadow: inset 0 0 3px hsla(0, 0%, 100%, .4)
  }

  .welcome_window .welcome_card .welcome_card_item {
    background: #f5faff;
    border: 1px solid #dbe5fd;
    border-radius: 8px;
    cursor: pointer;
    height: 102px;
    margin-bottom: 12px;
    overflow: hidden;
    padding: 12px 14px;
    position: relative;
    width: 220px
  }

  .welcome_window .welcome_card .welcome_card_item:hover {
    box-shadow: 0 4px 8px 0 rgba(112, 153, 208, .19);
    -webkit-transform: translateY(-1px);
    transform: translateY(-1px)
  }


  .welcome_window .welcome_card .welcome_card_item .welcome_item_desc {
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    color: #9295bf;
    display: -webkit-box;
    font-size: 12px;
    height: 38px;
    margin-top: 3px;
    overflow: hidden;
    text-overflow: ellipsis
  }


  .welcome_window .welcome_card .welcome_empty {
    height: 0;
    width: 294px
  }
}

@media screen and (max-width: 1601px) and (max-width: 1300px) {

  .welcome_window .welcome_list .welcome_content {
    width: 780px
  }

  .welcome_window .welcome_list .welcome_card .welcome_card_item {
    background: #f0f5fe;
    width: 240px
  }

  .welcome_window .welcome_list .welcome_card .welcome_empty {
    width: 240px
  }
}

@media screen and (min-width: 1601px) {


  .welcome_window .welcome_list {
    border-radius: 8px;
    color: #43436b;
    display: flex;
    font-size: 15px;
    height: auto;
    margin: 0 auto;
    min-height: 43px
  }

  .welcome_window .welcome_list .welcome_content {
    background: hsla(0, 0%, 100%, .5);
    border-radius: 8px;
    box-sizing: border-box;
    width: 950px
  }

  .welcome_window .welcome_list .welcome_title {
    align-items: center;
    color: #43436b;
    display: flex;
    font-size: 18px;
    justify-content: space-between;
    margin-bottom: 10px
  }

  .welcome_window .welcome_list .welcome_title .welcome_label > span {
    color: #b7b8cc;
    font-size: 12px;
    margin-left: 16px
  }


  .welcome_window .welcome_list .welcome_title img {
    height: auto;
    margin-right: 8px;
    width: 18px
  }


  .welcome_window .welcome_card {
    color: #43436b;
    display: flex;
    flex-wrap: wrap;
    height: auto;
    justify-content: left;
    margin-top: 18px;
    overflow-y: auto;
    padding-top: 1px;
    scrollbar-color: #dde7f7 transparent;
    scrollbar-width: thin;
    width: 100%
  }

  .welcome_window .welcome_card::-webkit-scrollbar {
    height: 0;
    width: 6px
  }

  .welcome_window .welcome_card::-webkit-scrollbar-thumb {
    background: #dde7f7;
    border-radius: 6px;
    -webkit-box-shadow: inset 0 0 3px hsla(0, 0%, 100%, .4)
  }

  .welcome_window .welcome_card .welcome_card_item {
    background: #f5faff;
    border: 1px solid #dbe5fd;
    border-radius: 8px;
    cursor: pointer;
    height: 80px;
    margin-bottom: 20px;
    overflow: hidden;
    padding: 12px 14px;
    position: relative;
    width: 220px
  }

  .welcome_window .welcome_card .welcome_card_item:hover {
    box-shadow: 0 4px 8px 0 rgba(112, 153, 208, .19);
    -webkit-transform: translateY(-1px);
    transform: translateY(-1px)
  }


  .welcome_window .welcome_card .welcome_card_item .welcome_item_desc {
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    color: #9295bf;
    display: -webkit-box;
    font-size: 12px;
    height: 38px;
    margin-top: 3px;
    overflow: hidden;
    text-overflow: ellipsis
  }


  .welcome_window .welcome_card .welcome_empty {
    height: 0;
    width: 294px
  }
}

@media screen and (min-width: 1601px) and (max-width: 1300px) {

  .welcome_window .welcome_list .welcome_content {
    width: 780px
  }

  .welcome_window .welcome_list .welcome_card .welcome_card_item {
    background: #f0f5fe;
    width: 240px
  }

  .welcome_window .welcome_list .welcome_card .welcome_empty {
    width: 240px
  }
}

.anwser_foot {
  width: 100%;
  height: 8rem;
  flex-shrink: 0
}

.sessions_option {
  display: flex;
  padding-bottom: .75rem;
  padding-top: .75rem;
  padding-left: .75rem;
  padding-right: .75rem;
  align-items: center;
  gap: .75rem;
  border-radius: .375rem;
  transition-duration: .15s;
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
  transition-timing-function: cubic-bezier(.4, 0, .2, 1);
  color: rgba(255, 255, 255, 1);
  cursor: pointer;
  font-size: .875rem;
  line-height: 1.25rem;

}

.sessions_option:hover {
  background-color: hsla(240, 9%, 59%, .1)
}

.login_img {
  text-align: center;
  margin: 0 auto;
  width: 200px;
  height: 200px;
}

.login_loading {
  text-align: center;
  margin: 0 auto;
}

.login_msg {
  text-align: center;
  margin-top: 40px
}

.edit_button {
  padding: .25rem
}

.edit_button:hover {
  color: rgba(255, 255, 255, 1)
}

.edit_options {
  position: absolute;
  display: flex;
  right: .25rem;
  z-index: 10;
  color: rgba(197, 197, 210, 1);
  visibility: visible;
}

.session_name {
  flex: 1 1 0%;
  max-height: 1.25rem;
  overflow: hidden;
  word-break: break-all;
  position: relative;
}

.session_box {
  display: flex;
  padding: .75rem;
  align-items: center;
  gap: .75rem;
  position: relative;
  border-radius: .375rem;
  cursor: pointer;
  word-break: break-all;
}

.session_selected {
  background-color: rgba(52, 53, 65, 1);
  padding-right: 3.5rem;
}

.session_selected:hover {
  background-color: rgba(52, 53, 65, 1)
}

.session_unselected:hover {
  background-color: rgba(42, 43, 50, 1);
  padding-right: 1rem;
}

.session_edit_input {
  font-size: .875rem;
  line-height: 1.25rem;
  border-style: none;
  background-color: transparent;
  padding: 0;
  margin: 0;
  width: 100%
}

.session_edit_list {
  display: flex;
  padding: .75rem;
  align-items: center;
  gap: .75rem;
  position: relative;
  border-radius: .375rem;
  cursor: pointer;
  word-break: break-all;
  padding-right: 3.5rem;
  background-color: rgba(52, 53, 65, 1)
}

.menu {
  background-color: #162540;
  position: fixed;
  bottom: 0;
  top: 0;
  display: flex;
  width: 260px;
  flex-direction: column;
}

.menu_list {
  flex-direction: column;
  min-height: 0;
  display: flex;
  height: 100%;
}

.menu_info {
  display: flex;
  width: 100%;
  height: 100%;
  flex: 1 1 0%;
  align-items: flex-start;
  border-color: hsla(0, 0%, 100%, .2);
}

.scrollbar-trigger ::-webkit-scrollbar-thumb {
  visibility: hidden
}

.scrollbar-trigger:hover ::-webkit-scrollbar-thumb {
  visibility: visible
}

.menu_nav {
  display: flex;
  height: 100%;
  flex: 1 1 0%;
  flex-direction: column;
  padding: .5rem
}

.menu_session_list {
  flex-direction: column;
  flex: 1 1 0%;
  overflow-y: auto;
  border-color: hsla(0, 0%, 100%, .2);
  border-bottom-width: 1px
}

.menu_sessions {
  display: flex;
  flex-direction: column;
  gap: .5rem;
  color: rgba(236, 236, 241, 1);
  font-size: .875rem;
  line-height: 1.25rem
}

.footer_info {
  gap: .75rem;
  margin-left: auto;
  margin-right: auto;
  max-width: 48rem;
  position: relative;
  height: 100%;
  flex: 1 1 0%;
  flex-direction: column;
  font-size: .75rem;
  line-height: 1rem;
  color: rgba(0, 0, 0, .5);
  padding-left: 1rem;
  padding-right: 1rem;
  padding-top: .75rem;
  padding-bottom: 1.5rem;
}

.footer_info.last-child {
  margin-bottom: 1.5rem
}

.session_input {
  width: 100%;
  flex-grow: 1;
  padding-bottom: .75rem;
  padding-top: .75rem;
  padding-left: 1rem;
  position: relative;
  border-width: 1px;
  border-color: rgba(0, 0, 0, .1);
  background-color: rgba(255, 255, 255, 1);
  border-radius: .375rem;

  box-shadow: 0 0 transparent, 0 0 transparent, 0 0 10px rgba(0, 0, 0, .1);
}

.session_input_title {
  width: fit-content;
  margin-right: 10px;
}

.session_send {
  width: fit-content;
  justify-content: flex-end;
  display: flex;
  flex-direction: column;
}

.session_form {
  display: flex;
  flex-direction: row;
  gap: .75rem;
  margin-left: auto;
  margin-right: auto;
  max-width: 48rem;
  padding-top: 1.5rem;
}

.session_form:last-child {
  margin-bottom: 1.5rem
}

.session_input_list {
  position: relative;
  height: 100%;
  flex: 1 1 0%;
  flex-direction: column;
}

.session_stop {
  display: flex;
  width: 100%;
  margin: auto;
  margin-bottom: .5rem;
  justify-content: center;
  gap: .5rem;
}

.handle_bottom {
  cursor: pointer;
  position: absolute;
  bottom: 120px;
  z-index: 10;
  border-radius: 9999px;
  border-width: 1px;
  border-color: rgba(217, 217, 227, 1);
  background-color: rgba(247, 247, 248, 1);
  color: rgba(86, 88, 105, 1);
  right: 20%;
  width: 40px;
  height: 40px;
  display: flex;
}

.md_head {
  background-color: rgba(0, 0, 0, 1);
  margin-bottom: 1rem;
  border-radius: .375rem;
}

.md_foot {
  padding: 1rem;
  overflow-y: auto;
}

.markdown_code {
  word-wrap: normal;
  background: none;
  color: #fff;
  -webkit-hyphens: none;
  hyphens: none;
  line-height: 1.5;
  tab-size: 4;
  text-align: left;
  white-space: pre;
  word-break: normal;
  word-spacing: normal
}

*, :after, :before {
  border: 0 solid #d9d9e3;
  box-sizing: border-box
}


html {
  -webkit-text-size-adjust: 100%;
  font-family: Söhne, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif, Helvetica Neue, Arial, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji;
  line-height: 1.5;
  tab-size: 4
}

body {
  line-height: inherit;
  margin: 0
}

a {
  color: inherit;
  text-decoration: inherit
}


button, input, optgroup, select, textarea {
  color: inherit;
  font-family: inherit;
  font-size: 100%;
  font-weight: inherit;
  line-height: inherit;
  margin: 0;
  padding: 0
}

button, select {
  text-transform: none
}

[type=button], [type=reset], [type=submit], button {
  -webkit-appearance: button;
  background-color: transparent;
  background-image: none
}


blockquote, dd, dl, fieldset, figure, h1, h2, h3, h4, h5, h6, hr, p, pre {
  margin: 0
}

fieldset, legend {
  padding: 0
}


textarea {
  resize: vertical
}


[role=button], button {
  cursor: pointer
}

:disabled {
  cursor: default
}

audio, canvas, embed, iframe, img, object, svg, video {
  display: block;
  vertical-align: middle
}

img, video {
  height: auto;
  max-width: 100%
}


::-webkit-scrollbar {
  height: 1rem;
  width: .5rem
}

::-webkit-scrollbar:horizontal {
  height: .5rem;
  width: 1rem
}

::-webkit-scrollbar-track {
  background-color: transparent;
  border-radius: 9999px
}

::-webkit-scrollbar-thumb {
  background-color: rgba(217, 217, 227, .8);
  border-color: rgba(255, 255, 255, 1);
  border-radius: 9999px;
  border-width: 1px
}

::-webkit-scrollbar-thumb:hover {
  background-color: rgba(236, 236, 241, 1)
}
</style>

