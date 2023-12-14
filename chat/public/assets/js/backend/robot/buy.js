define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'robot/buy/index' + location.search,
                    add_url: 'robot/buy/add?channel_id=0',
                    edit_url: 'robot/buy/edit',
                    del_url: 'robot/buy/del',
                    multi_url: 'robot/buy/multi',
                    import_url: 'robot/buy/import',
                    table: 'robot_buy',
                }
            });

            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                Table.api.init({
                    extend: {
                        index_url: 'robot/buy/index' + location.search,
                        add_url: 'robot/buy/add?channel_id='+$(this).data('value'),
                        edit_url: 'robot/buy/edit',
                        del_url: 'robot/buy/del',
                        multi_url: 'robot/buy/multi',
                        import_url: 'robot/buy/import',
                        table: 'robot_buy',
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
                search:false,
                searchFormVisible: true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'channel_id',visible:false,  title: __('Channel_id'),  formatter: Table.api.formatter.normal},
                        {field: 'channel.name', title: __('Channel.name'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'type', title: __('Type'), searchList: {"1":__('Type 1'),"2":__('Type 2'),"3":__('Type 3'),"4":__('Type 4'),"5":__('Type 5'),"6":__('Type 6'),"7":__('Type 7')}, formatter: Table.api.formatter.label},
                        {field: 'title', title: __('Title'), operate:false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'desc', title: __('Desc'), operate:false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'botton', title: __('Botton'), operate:false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'add_nums',operate:false, title: __('Add_nums')},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"0":__('Status 0')}, formatter: Table.api.formatter.toggle},
                        {field: 'createtime', title: __('Createtime'), operate:false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('观看历史'),
                                    title: function (row) {
                                        return row.channel.name+' - '+__('广告观看历史');
                                    },
                                    classname: 'btn btn-xs btn-danger btn-dialog',
                                    icon: 'fa fa-list',
                                    url: function (row) {
                                        return 'robot/addlist/index?channel_id='+row.channel_id+'&key=ad_limit';
                                    },
                                    refresh: true,
                                    visible: function (row) {
                                        if (row.type==4){
                                            return true;
                                        }
                                        return false;
                                    },
                                },

                                {
                                    name: 'Restore',
                                    text: __('分享历史'),
                                    title: function (row) {
                                        return row.channel.name+' - '+__('分享历史');
                                    },
                                    classname: 'btn btn-xs btn-warning btn-dialog',
                                    icon: 'fa fa-list',
                                    url: function (row) {
                                        return 'robot/addlist/index?channel_id='+row.channel_id+'&key=share_limit';
                                    },
                                    refresh: true,
                                    visible: function (row) {
                                        if (row.type==3){
                                            return true;
                                        }
                                        return false;
                                    },
                                },

                                {
                                    name: 'Restore',
                                    text: __('助力历史'),
                                    title: function (row) {
                                        return row.channel.name+' - '+__('助力历史');
                                    },
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-list',
                                    url: function (row) {
                                        return 'robot/help/index?channel_id='+row.channel_id;
                                    },
                                    refresh: true,
                                    visible: function (row) {
                                        if (row.type==2){
                                            return true;
                                        }
                                        return false;
                                    },
                                },

                                {
                                    name: 'Restore',
                                    text: __('订单列表'),
                                    title: function (row) {
                                        return row.channel.name+' - '+__('订单列表');
                                    },
                                    classname: 'btn btn-xs btn-info btn-addtabs',
                                    icon: 'fa fa-list',
                                    url: function (row) {
                                        return 'robot/order/index?channel_id='+row.channel_id;
                                    },
                                    refresh: true,
                                    visible: function (row) {
                                        if (row.type==1){
                                            return true;
                                        }
                                        return false;
                                    },
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
            $('#c-type').change(function(){
                $('#max_nums').hide();
                $('#c-max_nums').val('');
                $('#c-appid').val('');
                $('#c-path').val('');
                $('#price').hide();
                $('#appid').hide();
                $('#path').hide();
                $('#add_nums').hide();
                $('#img').hide();


                if($(this).val() == 1){
                    $('#c-max_nums').val('1');
                    $('#price').show();
                    $('#add_nums').show();
                }

                if($(this).val() == 2){
                    $('#max_nums').show();
                    $('#max_title').html('助力人数');
                    $('#add_nums').show();
                }

                if($(this).val() == 3 || $(this).val() == 4){
                    $('#max_nums').show();
                    $('#max_title').html('每日奖励限次');
                    $('#add_nums').show();
                }

                if($(this).val() == 5){
                    $('#appid').show();
                    $('#path').show();
                }

                if($(this).val() == 6){
                    $('#img').show();
                }

            })
            Controller.api.bindevent();
        },
        edit: function () {
            $(function(){
                if( $('#c-type').val() == 1){
                    $('#max_nums').hide();
                    $('#price').show();
                }

                if( $('#c-type').val() == 2){
                    $('#max_nums').show();
                    $('#max_title').html('助力人数');
                    $('#price').hide();
                }

                if( $('#c-type').val() == 3 || $('#c-type').val() == 4){
                    $('#max_nums').show();
                    $('#max_title').html('每日奖励限次');
                    $('#price').hide();
                }

                if($('#c-type').val() == 5){
                    $('#appid').show();
                    $('#path').show();
                    $('#add_nums').hide();
                }

                if($('#c-type').val() == 6){
                    $('#img').show();
                    $('#add_nums').hide();
                }

                if($('#c-type').val() == 7){
                    $('#add_nums').hide();
                }

            })
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
