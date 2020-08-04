/**
 * moodle-mod_groupformation JavaScript for editing group membership before saving to Moodle.
 * https://github.com/moodlepeers/moodle-mod_groupformation
 *
 *
 * @author Eduard Gallwas, Johannes Konert, René Röpke, Neora Wester, Ahmed Zukic, Stefan Jung
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const PAGE_SIZE = 10;

require(['jquery'], function ($) {
    $(document).ready(function () {
        let userData = $("#data").text();
        let data = JSON.parse(userData);
        selectPage(1);
        createPagination(data)

    });
});

/**
 * get called if the user is changing the pagination index
 * @param page
 */
function selectPage(page){
    let userData =document.getElementById("data").innerText;

    let data = JSON.parse(userData);
    let paginationArray = paginate(data, PAGE_SIZE, page)

    let tableHeader = ["#", "Vorname", "Nachname", "Consent Given", "Questionaire Answered", "Answers Submitted"]
    addTable(paginationArray, tableHeader, page);

}

/**
 * creates table
 * @param data
 * @param tableHeader
 * @param page
 */
function addTable(data, tableHeader, page) {
    let page_number = page -1;
    let tableContent = document.getElementById("table_content");

    let oldTable = tableContent.getElementsByClassName("table");

    // delete old table for the pagination index is change
    oldTable.length > 0 ? tableContent.removeChild(oldTable[0]) : null;

    // create table
    let table = document.createElement('TABLE');
    table.className = "table table-hover";

    // create table header
    let tableHead = document.createElement('THEAD');
    tableHead.className = "thead-light"
    table.appendChild(tableHead)

    let tr = document.createElement('TR');
    tableHead.appendChild(tr);
    for (let k = 0; k < tableHeader.length; k++) {
        let th = document.createElement('TH');
        th.scope = "col";
        th.appendChild(document.createTextNode(tableHeader[k]));
        tr.appendChild(th);
    }

    // create body
    let tableBody = document.createElement('TBODY');
    table.appendChild(tableBody);

    // add each item
    for (let i = 0; i < data.length; i++) {
        tr = document.createElement('TR');
        tableBody.appendChild(tr);


        // add index
        let td = document.createElement('TD');
        td.appendChild(document.createTextNode(page_number * PAGE_SIZE + i + 1));
        tr.appendChild(td);

        // add first name
        td = document.createElement('TD');
        td.appendChild(document.createTextNode(data[i][1].firstname));
        tr.appendChild(td);

        // add last name
        td = document.createElement('TD');
        td.appendChild(document.createTextNode(data[i][1].lastname));
        tr.appendChild(td);

        // add consent given
        td = document.createElement('TD');
        let consentIcon = data[i][0].consent === 0 ? renderXIcon() : renderCheckIcon();
        td.insertAdjacentHTML("beforeend", consentIcon);
        tr.appendChild(td);

        // add questionaire answered
        td = document.createElement('TD');
        td.appendChild(document.createTextNode(data[i][0].answer_count));
        tr.appendChild(td);

        // add answers submitted
        td = document.createElement('TD');
        let answeredIcon = data[i][0].completed === 0 ? renderXIcon() : renderCheckIcon();
        td.insertAdjacentHTML("beforeend", answeredIcon);
        tr.appendChild(td);

    }
    tableContent.appendChild(table);


}

/**
 * create pagination view
 * @param data
 */
function createPagination(data) {

    let pagination = document.getElementById("pagination");

    let numPages = data.length / PAGE_SIZE;

    // add an extra page
    if (numPages % 1 > 0)
        numPages = Math.floor(numPages) + 1;


    for (let i = 0; i < numPages ; i++) {
        let page = document.createElement("li");
        page.className = "pager-item";
        page.dataset.index = i;

        let a = document.createElement("a")
        a.className = "page-link";
        a.text = i +1;

        page.appendChild(a);

        if (i === 0)
            page.className = "page-item active";

        page.addEventListener('click', function() {
            console.log("click")
            let parent = this.parentNode;
            let items = parent.querySelectorAll(".page-item");
            for (let x = 0; x < items.length; x++) {
                items[x].className = "page-item"
            }
            this.className = "page-item active";
            let index = parseInt(this.dataset.index);

            console.log(index + 1)
            selectPage(index + 1);
            // loadTable(index);
        });
        pagination.appendChild(page);
    }
}

/**
 * calculate pagination index
 * @param array
 * @param page_size
 * @param page_number
 * @returns {*}
 */
function paginate(array, page_size, page_number) {
    // human-readable page numbers usually start with 1, so we reduce 1 in the first argument
    return array.slice((page_number - 1) * page_size, page_number * page_size);
}

/**
 * returns icon
 * @returns {string}
 */
function renderCheckIcon() {
    return "<svg width=\"1em\" height=\"1em\" viewBox=\"0 0 16 16\" class=\"bi bi-check-circle-fill\" fill=\"#43A047\" xmlns=\"http://www.w3.org/2000/svg\">\n" +
        "  <path fill-rule=\"evenodd\" d=\"M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z\"/>\n" +
        "</svg>"
}

/**
 * returns icon
 * @returns {string}
 */
function renderXIcon() {
    return "<svg width=\"1em\" height=\"1em\" viewBox=\"0 0 16 16\" class=\"bi bi-x-circle-fill\" fill=\"#e53935\" xmlns=\"http://www.w3.org/2000/svg\">\n" +
        "  <path fill-rule=\"evenodd\" d=\"M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.146-3.146a.5.5 0 0 0-.708-.708L8 7.293 4.854 4.146a.5.5 0 1 0-.708.708L7.293 8l-3.147 3.146a.5.5 0 0 0 .708.708L8 8.707l3.146 3.147a.5.5 0 0 0 .708-.708L8.707 8l3.147-3.146z\"/>\n" +
        "</svg>"
}

