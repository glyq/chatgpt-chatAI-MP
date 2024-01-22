define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'robot/channelmodel/index' + location.search,
                    add_url: 'robot/channelmodel/add'+location.search,
                    edit_url: 'robot/channelmodel/edit',
                    del_url: 'robot/channelmodel/del',
                    multi_url: 'robot/channelmodel/multi',
                    import_url: 'robot/channelmodel/import',
                    table: 'robot_channel_model',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),visible:false,operate:false},
                        {field: 'channel_id', title: __('应用id'),visible:false,},
                        {field: 'model.company', searchList: {"Wenxin":__('百度'),"Ali":__('阿里'),"Xunfei":__('科大讯飞'),"Ai360":__('360智脑'),"Chatglm":__('智谱ai'),"Chatgpt":__('OpenAi'),"Api2d":__('Api2d')},title: __('Model.company'),formatter: Table.api.formatter.normal},
                        {field: 'model.model_tag', title: __('Model.model_tag'), operate: 'LIKE', formatter: Table.api.formatter.flag},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            $('#channel_id').parent().parent().hide();

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
