define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'robot/model/index' + location.search,
                    add_url: 'robot/model/add',
                    edit_url: 'robot/model/edit',
                    del_url: 'robot/model/del',
                    multi_url: 'robot/model/multi',
                    import_url: 'robot/model/import',
                    table: 'robot_model',
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
                searchFormVisible: true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'company', title: __('Company'), searchList: {"Wenxin":__('百度'),"Ali":__('阿里'),"Xunfei":__('科大讯飞'),"Ai360":__('360智脑'),"Chatglm":__('智谱ai'),"Chatgpt":__('OpenAi'),"Api2d":__('Api2d')}, formatter: Table.api.formatter.normal},
                        {field: 'model_tag', title: __('Model_tag'), operate: 'LIKE', formatter: Table.api.formatter.label},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.toggle},
                        {field: 'createtime', title: __('Createtime'), operate: false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate: false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },


        add: function () {
            $('#c-company').change(function(){
                var model = $(this).val();
                $('#c-model_class').val(model);

                $('#c-appkey').val('');
                $('#c-appsecret').val('');
                $('#c-appid').val('');
                $('#c-authorization').val('');

                showinput(model)
            })
            Controller.api.bindevent();
        },
        edit: function () {
            var model = $('#c-company').val();
            $('#c-model_class').val(model);
            showinput(model)
            Controller.api.bindevent();


        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
        },
    };

    function showinput(model){
        $('#c-appkey').parent().parent().hide();
        $('#c-appsecret').parent().parent().hide();
        $('#c-appid').parent().parent().hide();
        $('#c-authorization').parent().parent().hide();



        $('#c-appkey').parent().prev().html('');
        $('#c-appsecret').parent().prev().html('');
        $('#c-appid').parent().prev().html('');
        $('#c-authorization').parent().prev().html('');

        $('#c-appkey').attr('data-rule','')
        $('#c-appsecret').attr('data-rule','')
        $('#c-authorization').attr('data-rule','')
        $('#c-appid').attr('data-rule','')

        switch (model){
            case 'Wenxin':
                $('#c-appkey').parent().parent().show();
                $('#c-appsecret').parent().parent().show();
                $('#c-appkey').attr('data-rule','required,length(~32)')
                $('#c-appsecret').attr('data-rule','required,length(~32)')
                $('#c-appkey').parent().prev().html('API Key');
                $('#c-appsecret').parent().prev().html('Secret Key');
                break;
            case 'Ali':
            case 'Ai360':
            case 'Chatgpt':
            case 'Api2d':
                $('#c-authorization').parent().parent().show();
                $('#c-authorization').parent().prev().html('API-KEY');
                $('#c-authorization').attr('data-rule','required,length(~128)')
                break;
            case 'Chatglm':
                $('#c-appkey').parent().parent().show();
                $('#c-appsecret').parent().parent().show();
                $('#c-appkey').attr('data-rule','required,length(~32)')
                $('#c-appsecret').attr('data-rule','required,length(~32)')
                $('#c-appkey').parent().prev().html('API Key.id');
                $('#c-appsecret').parent().prev().html('API Key.secret');
                break;
            case 'Xunfei':
                $('#c-appkey').parent().parent().show();
                $('#c-appsecret').parent().parent().show();
                $('#c-appid').parent().parent().show();
                $('#c-appkey').attr('data-rule','required,length(~32)')
                $('#c-appsecret').attr('data-rule','required,length(~32)')
                $('#c-appid').attr('data-rule','required,length(~32)')
                $('#c-appkey').parent().prev().html('APIKey');
                $('#c-appsecret').parent().prev().html('APISecret');
                $('#c-appid').parent().prev().html('APPID');
                break;
            default:
                break;
        }
    }
    return Controller;
});
