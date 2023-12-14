define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'robot/user/index' + location.search,
                    add_url: 'robot/user/add',
                    edit_url: 'robot/user/edit',
                    del_url: 'robot/user/del',
                    multi_url: 'robot/user/multi',
                    import_url: 'robot/user/import',
                    table: 'robot_user',
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
                        {field: 'channel.name', title: __('Channel.name'), operate:false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'nickname', title: __('Nickname'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'head_img', title: __('Head_img'), operate:false, table: table, class: 'autocontent', formatter: Table.api.formatter.image},
                        {field: 'desc',operate:false, title: __('Desc'), table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'openid', title: __('Openid'),operate: 'LIKE'},
                        {field: 'unionid',visible:false,operate:false, title: __('Unionid')},
                        {field: 'channel_id',visible:false, title: __('Channel_id'), formatter: Table.api.formatter.normal},
                        {field: 'phone',operate:'like', title: __('Phone')},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.toggle},
                        {field: 'platform',visible:false, title: __('Platform'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime',visible:false, title: __('Updatetime'), operate:false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'vip.num', title: __('Vip.num'),operate:'>='},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('用户token'),
                                    title: __('用户token'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-list',
                                    url: 'robot/usertoken/index?user_id={ids}',
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
