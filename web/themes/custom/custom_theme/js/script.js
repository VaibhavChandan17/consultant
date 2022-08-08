jQuery(document).ready(function () {
    GetBindData()
});

//Get all employees
function GetBindData() {
    jQuery.ajax({
        url: 'http://localhost/drupal_cms/drupal9/web/api/Lucifer',
        method: 'GET',
        dataType: 'json',
        //contentType: 'application/json; charset=utf-8',
        success: function (result)
        {
            //after successfully call bind data to Table
            BindDataToTable(result);
        },
    })
}
function BindDataToTable(data)
{
    if (data != null && data) {
        for (var i = 0; i < data.length; i++) {
            var tablerow = "<tr>"
                          //+ "<td>" + data[i].Id + "</td>"
                          + "<td>" + data[i].EmpID + "</td>"
                          + "<td>" + data[i].Paragraph + "</td>"
                          + "</tr>";
            jQuery("#tblbody").append(tablerow);
        }
    }
}