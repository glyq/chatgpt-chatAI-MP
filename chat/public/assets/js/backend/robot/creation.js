define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'robot/creation/index' + location.search,
                    add_url: 'robot/creation/add',
                    edit_url: 'robot/creation/edit',
                    del_url: 'robot/creation/del',
                    multi_url: 'robot/creation/multi',
                    import_url: 'robot/creation/import',
                    table: 'robot_creation',
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
                search:false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'channel_id', visible:false,title: __('Channel_id'), formatter: Table.api.formatter.normal},
                        {field: 'channel.name', title: __('Channel.name'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'assistant.name', title: __('Assistant.name'), operate: 'LIKE', table: table, class: 'autocontent', formatter: function(value, row){var type_name = value == null ? '对话' : value;
                                return type_name;
                            }},
                        {field: 'model', title: __('Model'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.tag},

                        {field: 'input', title: __('Input'), operate: false, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'msg', title: __('Msg'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},

                        {field: 'ip', title: __('Ip'), operate: 'like', table: table, class: 'autocontent', formatter: Table.api.formatter.ip},
                        {field: 'tokens', title: __('Tokens'),operate:'between'},
                        {field: 'time', title: __('Time'), operate:'between', autocomplete:false, formatter: Table.api.formatter.content},
                        {field: 'stream', title: __('Stream'), searchList: {"2":__('Stream 2'),"1":__('Stream 1')}, formatter: Table.api.formatter.normal},
                        {field: 'rate', title: __('Rate'), searchList: {"0":__('Rate 0'),"1":__('Rate 1'),"2":__('Rate 2'),"3":__('Rate 3'),"4":__('Rate 4'),"5":__('Rate 5')}, formatter: Table.api.formatter.normal},
                        {field: 'platform', title: __('Platform'), searchList: {"wx":__('Platform wx'),"pc":__('Platform pc')}, formatter: Table.api.formatter.normal},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},

                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('详情'),
                                    title: function (row) {
                                        return row.channel.name+' - '+row.assistant.name+' - '+__('详情');
                                    },
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-list',
                                    url: 'robot/creation/detail?ids={ids}',
                                    refresh: true,

                                },

                            ],
                        }
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
