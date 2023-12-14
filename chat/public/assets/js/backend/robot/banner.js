define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'robot/banner/index' + location.search,
                    add_url: 'robot/banner/add?channel_id=0',
                    edit_url: 'robot/banner/edit',
                    del_url: 'robot/banner/del',
                    multi_url: 'robot/banner/multi',
                    import_url: 'robot/banner/import',
                    table: 'robot_banner',
                }
            });

            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                Table.api.init({
                    extend: {
                        index_url: 'robot/banner/index' + location.search,
                        add_url: 'robot/banner/add?channel_id='+$(this).data('value'),
                        edit_url: 'robot/banner/edit',
                        del_url: 'robot/banner/del',
                        multi_url: 'robot/banner/multi',
                        import_url: 'robot/banner/import',
                        table: 'robot_banner',
                    }
                });
            })

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh desc,id desc',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'channel.name', title: __('Channel.name'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'name', title: __('Name'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'img', title: __('Img'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.image},
                        {field: 'channel_id', title: __('Channel_id'),visible:false,formatter: Table.api.formatter.normal},
                        {field: 'jump_type', title: __('Jump_type'), searchList: {"1":__('Jump_type 1'),"0":__('Jump_type 0')}, formatter: Table.api.formatter.normal},
                        {field: 'appid', title: __('Appid'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'path', title: __('Path'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.toggle},
                        {field: 'createtime', title: __('Createtime'), operate: false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate: false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},

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
