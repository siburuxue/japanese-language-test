/**
 * 通过规则加载权限树
 */
function loadPermission(){
    let role = $('#role').val().join();
    let url = Routing.generate('permission-tree', {type:"checkbox",role});
    $('#permission-tree').data('load-url',url).loadData();
}

/**
 * 获取权限树被选中的节点ID
 * @returns array 被选中的ID数组
 */
function getSelectedPermission(){
    let p1 = $('.jstree-clicked').map(function(){ return $(this).parent().data('id'); }).get();
    let p2 = $('.jstree-undetermined').map(function(){ return $(this).parent().parent().data('id'); }).get();
    return p1.concat(p2);
}

/**
 * ajax 全局设置
 * header中添加csrf信息
 */
$.ajaxSetup({
    headers:{
        "csrf-token": $('input[type=hidden][name=csrf-token]').val()
    }
});