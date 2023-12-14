define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'robot/assistant/index' + location.search,
                    add_url: 'robot/assistant/add',
                    edit_url: 'robot/assistant/edit',
                    del_url: 'robot/assistant/del',
                    multi_url: 'robot/assistant/multi',
                    import_url: 'robot/assistant/import',
                    table: 'robot_assistant',
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
                searchFormVisible: true,
                search:false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'cate.name', title: __('Cate.name'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.label},
                        {field: 'desc', title: __('Desc'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'icon', title: __('Icon'), operate: false, formatter: Table.api.formatter.image},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, formatter:Table.api.formatter.toggle},
                        {field: 'createtime', title: __('Createtime'), operate: false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate: false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},

                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            table.on('post-body.bs.table', function (e, settings, json, xhr) {
                $(".btn-editone").data("area", ["90%", "90%"]);
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },


        add: function () {
            $(document).on("fa.event.appendfieldlist", "#first-table .btn-append", function (e, obj) {
                //绑定动态下拉组件
                Form.events.selectpage(obj);
                $(".fieldlist", $("form[role=form]")).on("click", ".btn-remove", function () {
                    var container = $(".fieldlist");
                    refresh(container);
                });

            });

            function refresh(container){
                var data = {};
                var textarea = $("#c-template");
                $.each($("input,select,textarea", container).serializeArray(), function (i, j) {
                    var reg = /\[(\w+)\]\[(\w+)\]$/g;
                    var match = reg.exec(j.name);
                    if (!match)
                        return true;
                    match[1] = "x" + parseInt(match[1]);
                    if (typeof data[match[1]] == 'undefined') {
                        data[match[1]] = {};
                    }
                    data[match[1]][match[2]] = j.value;
                });
                var name = $("#c-name").val();
                var msg = '请参考以下要求生成';
                msg += (name ? name : '内容') + '："'
                $.each(data, function (i, j) {
                    if (j) {
                        if((j.title != '') && (j.tag != '')){
                            msg += j.title + '为：[[' + j.tag + "]]。";
                        }
                    }
                });
                msg += '"'

                textarea.val(msg);
            };



            $(document).on('change keyup changed', ".fieldlist input,.fieldlist textarea,.fieldlist select", function () {
                var container = $(this).closest(".fieldlist");
                refresh(container);
            });





            Controller.api.bindevent();
        },
        edit: function () {

            $(document).on("fa.event.appendfieldlist", "#first-table .btn-append", function (e, obj) {
                //绑定动态下拉组件
                Form.events.selectpage(obj);
                $(".fieldlist", $("form[role=form]")).on("click", ".btn-remove", function () {
                    var container = $(".fieldlist");
                    refresh(container);
                });

            });

            function refresh(container){
                var data = {};
                var textarea = $("#c-template");
                $.each($("input,select,textarea", container).serializeArray(), function (i, j) {
                    var reg = /\[(\w+)\]\[(\w+)\]$/g;
                    var match = reg.exec(j.name);
                    if (!match)
                        return true;
                    match[1] = "x" + parseInt(match[1]);
                    if (typeof data[match[1]] == 'undefined') {
                        data[match[1]] = {};
                    }
                    data[match[1]][match[2]] = j.value;
                });
                var name = $("#c-name").val();
                var msg = '请参考以下要求生成';
                msg += (name ? name : '内容') + '："'
                $.each(data, function (i, j) {
                    if (j) {
                        if((j.title != '') && (j.tag != '')){
                            msg += j.title + '为：[[' + j.tag + "]]。";
                        }
                    }
                });
                msg += '"'

                textarea.val(msg);
            };



            $(document).on('change keyup changed', ".fieldlist input,.fieldlist textarea,.fieldlist select", function () {
                var container = $(this).closest(".fieldlist");
                refresh(container);
            });


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
