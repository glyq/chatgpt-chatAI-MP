define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'robot/notice/index' + location.search,
                    add_url: 'robot/notice/add?channel_id=0',
                    edit_url: 'robot/notice/edit',
                    del_url: 'robot/notice/del',
                    multi_url: 'robot/notice/multi',
                    import_url: 'robot/notice/import',
                    table: 'robot_notice',
                }
            });

            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                Table.api.init({
                    extend: {
                        index_url: 'robot/notice/index' + location.search,
                        add_url: 'robot/notice/add?channel_id='+$(this).data('value'),
                        edit_url: 'robot/notice/edit',
                        del_url: 'robot/notice/del',
                        multi_url: 'robot/notice/multi',
                        import_url: 'robot/notice/import',
                        table: 'robot_notice',
                    }
                });
            })

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh desc,id desc',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'channel_id',visible:false, title: __('Channel_id'), formatter: Table.api.formatter.normal},
                        {field: 'channel.name', title: __('Channel.name'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'title', title: __('Title'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'img', title: __('Img'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.image},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.toggle},
                        {field: 'createtime', title: __('Createtime'), operate: false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
