!function (e) {
    var t = {};

    function r(a) {
        if (t[a]) return t[a].exports;
        var o = t[a] = {i: a, l: !1, exports: {}};
        return e[a].call(o.exports, o, o.exports, r), o.l = !0, o.exports
    }

    r.m = e, r.c = t, r.d = function (e, t, a) {
        r.o(e, t) || Object.defineProperty(e, t, {enumerable: !0, get: a})
    }, r.r = function (e) {
        "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(e, Symbol.toStringTag, {value: "Module"}), Object.defineProperty(e, "__esModule", {value: !0})
    }, r.t = function (e, t) {
        if (1 & t && (e = r(e)), 8 & t) return e;
        if (4 & t && "object" == typeof e && e && e.__esModule) return e;
        var a = Object.create(null);
        if (r.r(a), Object.defineProperty(a, "default", {
            enumerable: !0,
            value: e
        }), 2 & t && "string" != typeof e) for (var o in e) r.d(a, o, function (t) {
            return e[t]
        }.bind(null, o));
        return a
    }, r.n = function (e) {
        var t = e && e.__esModule ? function () {
            return e.default
        } : function () {
            return e
        };
        return r.d(t, "a", t), t
    }, r.o = function (e, t) {
        return Object.prototype.hasOwnProperty.call(e, t)
    }, r.p = "/", r(r.s = 492)
}({
    492: function (e, t, r) {
        e.exports = r(493)
    }, 493: function (e, t) {
        !function () {
            "use strict";
            var e = function (e) {
                var t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : "line",
                    r = arguments.length > 2 && void 0 !== arguments[2] ? arguments[2] : {}, a = {
                        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                        datasets: [{label: "Performance", data: [0, 10, 5, 15, 10, 20, 15, 25, 20, 30, 25, 40]}]
                    };
                Charts.create(e, t, r, a)
            }, t = function (e) {
                var t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : "roundedBar",
                    r = arguments.length > 2 && void 0 !== arguments[2] ? arguments[2] : {}, a = {
                        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                        datasets: [{
                            label: "Sales",
                            data: [25, 20, 30, 22, 17, 10, 18, 26, 28, 26, 20, 32],
                            barThickness: 20
                        }]
                    };
                Charts.create(e, t, r, a)
            }, r = function (e, t, r) {
                var a = arguments.length > 3 && void 0 !== arguments[3] ? arguments[3] : "doughnut",
                    o = arguments.length > 4 && void 0 !== arguments[4] ? arguments[4] : {};
                o = Chart.helpers.merge({
                    cutoutPercentage: 85,
                    aspectRatio: 1,
                    responsive: !1,
                    maintainAspectRatio: !1
                }, o);
                var n = {datasets: [{data: [t, r - t], borderWidth: 0}]};
                Charts.create(e, a, o, n)
            };
            r("#inTimeProgressChart", 24.84, 27), r("#lateProgressChart", 6.21, 27), r("#absentsProgressChart", 1.62, 27), r("#vacationProgressChart", .27, 27), e("#performanceChart"), e("#performanceAreaChart", "area"), t("#ordersChart"), t("#ordersChartSwitch"), function (e) {
                var t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : "doughnut",
                    r = arguments.length > 2 && void 0 !== arguments[2] ? arguments[2] : {}, a = {
                        labels: ["Desktop", "Tablet", "Mobile"],
                        datasets: [{data: [60, 25, 15], backgroundColor: [], hoverBorderColor: settings.colors.white}]
                    };
                Charts.create(e, t, r, a)
            }("#devicesChart"), function (e) {
                var t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : "radar",
                    r = arguments.length > 2 && void 0 !== arguments[2] ? arguments[2] : {}, a = {
                        labels: ["JavaScript", "HTML", "Flinto", "Vue.js", "Sketch", "Priciple", "CSS", "Angular"],
                        datasets: [{
                            label: "Experience IQ",
                            data: [30, 35, 33, 32, 31, 30, 28, 36],
                            pointHoverBorderColor: settings.colors.accent[400],
                            pointHoverBackgroundColor: settings.colors.white
                        }]
                    };
                Charts.create(e, t, r, a)
            }("#topicIqChart"), $('[data-toggle="chart"]:checked').each((function (e, t) {
                Charts.add($(t))
            }))
        }()
    }
});