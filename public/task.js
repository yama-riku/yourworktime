// 時計の実装------------------------------------------------------
function set2(num) {
    // 桁数が1桁の場合に０埋めを行う
    let ret;
    if (num < 10) { ret = "0" + num; }
    else { ret = num; }
    return ret;          
}

function showClock() {
    // 現在の時刻
    const nowTime = new Date();
    // 時間、分、秒
    const nowHour = set2(nowTime.getHours());
    const nowMin = set2(nowTime.getMinutes());
    const nowSec =  set2(nowTime.getSeconds());
    const msg = nowHour + ":" + nowMin + ":" + nowSec;
    document.getElementById("showTime").innerHTML = msg;
}
// 紙芝居的な感じで毎秒映し出せる
setInterval('showClock()',1000);

// // ボタンの非活性-------------------------------------------------------
// function func1()  {
//     document.getElementById("startButton1").disabled = true;
//     document.getElementById("endButton1").disabled = false;
//     // document.getElementById("output");
// }

// function func2()  {
//     document.getElementById("startButton1").disabled = false;
//     document.getElementById("endButton1").disabled = true;
//     // document.getElementById("output");
// }