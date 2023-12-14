define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'robot/help/index' + location.search,
                    add_url: 'robot/help/add',
                    edit_url: 'robot/help/edit',
                    del_url: 'robot/help/del',
                    multi_url: 'robot/help/multi',
                    import_url: 'robot/help/import',
                    table: 'robot_help',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                search:false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'channel_id', title: __('Channel_id'),visible:false, formatter: Table.api.formatter.normal},
                        {field: 'channel.name', title: __('Channel.name'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'pid', title: __('Pid')},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
