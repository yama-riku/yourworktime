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

// ハンバーガーメニューの実装
window.onload = function() {
    var nav = document.getElementById('nav-wrapper');
    var hamburger = document.getElementById('js-hamburger');
    var blackBg = document.getElementById('js-black-bg');

    hamburger.addEventListener('click',function() {
        nav.classList.toggle('open');        
    });
    blackBg.addEventListener('click',function()
    {
        nav.classList.remove('open');
    });
}

