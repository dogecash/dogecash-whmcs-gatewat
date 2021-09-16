////////////////////////////////
// Countdown timer for transaction verification
////////////////////////////////

$(function () {
  let address = $('input[name="address"]')[0].value;
  let amount = $('input[name="amount"]')[0].value;
  let order_time = $('input[name="order_time"]')[0].value;
  let confirmations = $('input[name="confirmations"]')[0].value;
  let max_time = $('input[name="maxtime"]')[0].value;

  let cpCount = order_time - Math.floor(Date.now() / 1000) + max_time * 60;

  let cpCounter = setInterval(timer, 1000);
  var validation = setInterval(validation, 10000);

  function formatTime(seconds) {
    var h = Math.floor(seconds / 3600),
      m = Math.floor(seconds / 60) % 60,
      s = seconds % 60;
    if (h < 10) h = "0" + h;
    if (m < 10) m = "0" + m;
    if (s < 10) s = "0" + s;
    return m + ":" + s;
  }

  function timeout() {
    $('input[name="x_status"]')[0].value = false;
    $("#callback-form").submit();
  }

  function timer() {
    cpCount--;
    if (cpCount <= 0) {
      timeout();
    }
    $(".cp-counter").html(formatTime(cpCount));
  }

  async function validation() {
    const BASE_URL = "https://payment-checker.dogecash.org/";
    const QUERY_URL = `${BASE_URL}?address=${address}&amount=${amount}&tx=missing&otime=${order_time}&conf=${confirmations}&mtime=${max_time}`;

    let request = await fetch(QUERY_URL);
    data = await request.json();

    if (data["status"] == "detected") {
      $(
        ".cp-payment-info-status"
      )[0].innerHTML = `Transaction detected! Waiting for confirmations (${data["confirmations"]}/${confirmations})`;
    }

    if (data["status"] == "confirmed") {
      $('input[name="x_status"]')[0].value = true;
      $('input[name="x_txid"]')[0].value = data["transaction_id"];
      $("#callback-form").submit();
    }
  }

  ////////////////////////////////
  // Copy button action
  ////////////////////////////////
  $(document).on("click", ".cp-copy-btn", function (e) {
    var btn = $(this);
    var input = btn.closest(".cp-input-box").find("input");

    input.select();
    document.execCommand("Copy");

    btn.addClass("cp-copied");
    setTimeout(function () {
      btn.removeClass("cp-copied");
    }, 1000);
  });
});
