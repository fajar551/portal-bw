var options = {
        series: [
            {
                name: "2020",
                type: "column",
                data: [23, 42, 35, 27, 43, 22, 17, 31, 22, 22, 12, 16]
            },
            {
                name: "2019",
                type: "line",
                data: [23, 32, 27, 38, 27, 32, 27, 38, 22, 31, 21, 16]
            }
        ],
        chart: { height: 2120, type: "line", toolbar: { show: !1 } },
        stroke: { width: [0, 3], curve: "smooth" },
        plotOptions: { bar: { horizontal: !1, columnWidth: "20%" } },
        dataLabels: { enabled: !1 },
        legend: { show: !1 },
        colors: ["#5664d2", "#1cbb8c"],
        labels: [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec"
        ]
    },
    chart = new ApexCharts(
        document.querySelector("#line-column-chart"),
        options
    );
chart.render();
options = {
    series: [42, 26, 15],
    chart: { height: 230, type: "donut" },
    labels: ["Product A", "Product B", "Product C"],
    plotOptions: { pie: { donut: { size: "75%" } } },
    dataLabels: { enabled: !1 },
    legend: { show: !1 },
    colors: ["#5664d2", "#1cbb8c", "#eeb902"]
};
options = {
    series: [{ data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0] }],
    chart: { type: "line", width: 120, height: 35, sparkline: { enabled: !0 } },
    stroke: { width: [3], curve: "smooth" },
    colors: ["#79CED0"],
    tooltip: {
        fixed: { enabled: !1 },
        x: { show: !1 },
        y: {
            title: {
                formatter: function(e) {
                    return "Today";
                }
            }
        },
        marker: { show: !1 }
    }
};
(chart = new ApexCharts(
    document.querySelector("#spak-chart1"),
    options
)).render();
options = {
    series: [{ data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0] }],
    chart: { type: "line", width: 120, height: 35, sparkline: { enabled: !0 } },
    stroke: { width: [3], curve: "smooth" },
    colors: ["#FABD78"],
    tooltip: {
        fixed: { enabled: !1 },
        x: { show: !1 },
        y: {
            title: {
                formatter: function(e) {
                    return "";
                }
            }
        },
        marker: { show: !1 }
    }
};
(chart = new ApexCharts(
    document.querySelector("#spak-chart2"),
    options
)).render();
options = {
    series: [{ data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0] }],
    chart: { type: "line", width: 120, height: 35, sparkline: { enabled: !0 } },
    stroke: { width: [3], curve: "smooth" },
    colors: ["#5664d2"],
    tooltip: {
        fixed: { enabled: !1 },
        x: { show: !1 },
        y: {
            title: {
                formatter: function(e) {
                    return "";
                }
            }
        },
        marker: { show: !1 }
    }
};
(chart = new ApexCharts(
    document.querySelector("#spak-chart3"),
    options
)).render();
options = {
    series: [{ data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0] }],
    chart: { type: "line", width: 120, height: 35, sparkline: { enabled: !0 } },
    stroke: { width: [3], curve: "smooth" },
    colors: ["#A8E4B1"],
    tooltip: {
        fixed: { enabled: !1 },
        x: { show: !1 },
        y: {
            title: {
                formatter: function(e) {
                    return "";
                }
            }
        },
        marker: { show: !1 }
    }
};
(chart = new ApexCharts(
    document.querySelector("#spak-chart4"),
    options
)).render();
options = {
    series: [{ data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0] }],
    chart: { type: "line", width: 120, height: 35, sparkline: { enabled: !0 } },
    stroke: { width: [3], curve: "smooth" },
    colors: ["#F086B4"],
    tooltip: {
        fixed: { enabled: !1 },
        x: { show: !1 },
        y: {
            title: {
                formatter: function(e) {
                    return "";
                }
            }
        },
        marker: { show: !1 }
    }
};
(chart = new ApexCharts(
    document.querySelector("#spak-chart5"),
    options
)).render();
options = {
    series: [{ data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0] }],
    chart: { type: "line", width: 120, height: 35, sparkline: { enabled: !0 } },
    stroke: { width: [3], curve: "smooth" },
    colors: ["#B161D6"],
    tooltip: {
        fixed: { enabled: !1 },
        x: { show: !1 },
        y: {
            title: {
                formatter: function(e) {
                    return "";
                }
            }
        },
        marker: { show: !1 }
    }
};
(chart = new ApexCharts(
    document.querySelector("#spak-chart6"),
    options
)).render(),
    $("#usa-vectormap").vectorMap({
        map: "us_merc_en",
        backgroundColor: "transparent",
        regionStyle: {
            initial: {
                fill: "#e8ecf4",
                stroke: "#74788d",
                "stroke-width": 1,
                "stroke-opacity": 0.4
            }
        }
    }),
    $(document).ready(function() {
        $(".datatable").DataTable({
            lengthMenu: [5, 10, 25, 50],
            pageLength: 5,
            columns: [
                { orderable: !1 },
                { orderable: !0 },
                { orderable: !0 },
                { orderable: !0 },
                { orderable: !0 },
                { orderable: !0 },
                { orderable: !1 }
            ],
            order: [[1, "asc"]],
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function() {
                $(".dataTables_paginate > .pagination").addClass(
                    "pagination-rounded"
                );
            }
        });
    });

// const swiper = new Swiper(".swiper-container", {
//     // Optional parameters
//     direction: "horizontal",
//     loop: false,

//     // If we need pagination
//     pagination: {
//         el: ".swiper-pagination"
//     },

//     // Navigation arrows
//     navigation: {
//         nextEl: ".swiper-button-next",
//         prevEl: ".swiper-button-prev"
//     },

//     // And if we need scrollbar
//     scrollbar: {
//         el: ".swiper-scrollbar",
//         hide: true
//     }
// });
