define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'robot/channelassistant/index' + location.search,
                    add_url: 'robot/channelassistant/add'+ location.search+'&cate_id=0',
                    edit_url: 'robot/channelassistant/edit',
                    del_url: 'robot/channelassistant/del',
                    multi_url: 'robot/channelassistant/multi',
                    import_url: 'robot/channelassistant/import',
                    table: 'robot_channel_assistant',
                }
            });

            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                Table.api.init({

                    extend: {
                        index_url: 'robot/channelassistant/index' + location.search,
                        add_url: 'robot/channelassistant/add'+ location.search+'&cate_id='+$(this).data('value'),
                        edit_url: 'robot/channelassistant/edit',
                        del_url: 'robot/channelassistant/del',
                        multi_url: 'robot/channelassistant/multi',
                        import_url: 'robot/channelassistant/import',
                        table: 'robot_channel_assistant',
                    }
                });
            })

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate: false,},
                        {field: 'channel_id', title: __('应用id'), visible:false,operate: '=', formatter: Table.api.formatter.normal},
                        {field: 'assistant.cate_id', title: __('分类id'),visible:false, operate: '=', formatter: Table.api.formatter.normal},
                        {field: 'assistant.name', title: __('Assistant.name'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'assistant.icon', title: __('Assistant.icon'), operate: false, formatter: Table.api.formatter.image},
                        {field: 'assistant.desc', title: __('Assistant.desc'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            $('#channel_id').parent().parent().hide();
            $("#assistant\\.cate_id").parent().parent().hide();

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
