define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'robot/channel/index' + location.search,
                    add_url: 'robot/channel/add',
                    edit_url: 'robot/channel/edit',
                    del_url: 'robot/channel/del',
                    multi_url: 'robot/channel/multi',
                    import_url: 'robot/channel/import',
                    table: 'robot_channel',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'free_num',operate:false, title: __('Free_num')},
                        {field: 'stream',operate:false, title: __('流式输出'), searchList: {"2":__('Stream 2'),"1":__('Stream 1')},yes:2,no:1, formatter: Table.api.formatter.toggle},

                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.toggle},
                        {field: 'show_stream', title: __('选择输出方式按钮'),operate:false, searchList: {"1":__('Show_stream 1'),"0":__('Show_stream 0')}, formatter: Table.api.formatter.toggle},
                        {field: 'show_vip', title: __('Show_vip'),operate:false, searchList: {"0":__('Show_vip 0'),"1":__('Show_vip 1')}, formatter: Table.api.formatter.toggle},
                        {field: 'show_ad', title: __('Show_ad'),operate:false, searchList: {"0":__('Show_ad 0'),"1":__('Show_ad 1')}, formatter: Table.api.formatter.toggle},
                        {field: 'createtime', title: __('Createtime'),operate:false,  addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'),operate:false,  addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    text: __('关联模型'),
                                    title: function (row) {
                                        return row.name+' - '+__('关联模型');
                                    },
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-list',
                                    url: 'robot/channelmodel/index?channel_id={ids}',
                                    refresh: true,

                                },
                                {
                                    text: __('关联分类'),
                                    title: function (row) {
                                        return row.name+' - '+__('关联分类');
                                    },
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-folder-o',
                                    url: 'robot/channelcate/index?channel_id={ids}',
                                    refresh: true,

                                },

                                {
                                    text: __('关联助手'),
                                    title: function (row) {
                                        return row.name+' - '+__('关联助手');
                                    },
                                    classname: 'btn btn-xs btn-warning btn-dialog',
                                    icon: 'fa fa-folder',
                                    url: 'robot/channelassistant/index?channel_id={ids}',
                                    refresh: true,

                                },
                            ],
                        }
                    ]
                ]
            });

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
