$(function () {
  $("#settings_table tbody").each(function () {
    $(this).hide();
    $(this).addClass("hide");
  });
  $("#settings_table thead").click(function () {
    let tbody = $(this).next("tbody");

    if ($(tbody).hasClass("hide")) {
      tbody.show();
      tbody.removeClass("hide");
      $(this).find("tr").css("background-color", "#a5c0ee");
    } else {
      tbody.hide();
      tbody.addClass("hide");
      $(this).find("tr").css("background-color", "#ccdbf5");
    }
  });

  //////////////////////////
  // PLATFORM SETTINGS PANEL
  ////////////////////////

  let settings_pattern = {
    selector_id: "platform_setting",
    new_button_id: "new_setting",
    new_pattern_id: "new_platform_setting",
    settings_selector_id: "",
    save_button_id: "save_setting",
    remove_email_id: "remove_setting",
    fields: [
      {
        key: "setting_pattern_name",
        attr: "data-name",
        important: 1,
      },
      {
        key: "setting_description",
        attr: "data-description",
        important: 0,
      },
      {
        key: "setting_email_pattern",
        attr: "data-email_pattern",
        important: 1,
      },
    ],
    ajax: {
      url: "./manage/settings/logic",
    },
  };

  let email_pattern = {
    selector_id: "email_pattern",
    settings_selector_id: "setting_email_pattern",
    new_button_id: "new_email",
    new_pattern_id: "new_email_pattern",
    save_button_id: "save_email",
    remove_email_id: "remove_email",
    fields: [
      {
        key: "email_pattern_name",
        attr: "data-pattern_name",
        important: 1,
      },
      {
        key: "email_description",
        attr: "data-description",
        important: 0,
      },
      {
        key: "admin_email",
        attr: "data-admin-email",
        important: 1,
      },
      {
        key: "reception_email",
        attr: "data-reception-email",
        important: 1,
      },
      {
        key: "user_email",
        attr: "data-user_email",
        important: 0,
      },
      {
        key: "manager_email",
        attr: "data-manager_email",
        important: 0,
      },
    ],
    ajax: {
      url: "/platform/manage/email/logic",
    },
  };

  function createCorrelation(relation) {
    // get date form server
    function sendAjax(u, d) {
      $.ajax({
        type: "POST",
        url: u,
        data: d,
        success: function (result) {
          if (d["logic"] == "get") {
            $("option", $("#" + relation["selector_id"])).each(function () {
              if ($(this).val() != "") {
                $(this).remove();
              }
            });

            $("option", $("#" + relation["settings_selector_id"])).each(
              function () {
                $(this).remove();
              }
            );

            $("#" + relation["selector_id"]).append(result);
            if (relation["settings_selector_id"] != "")
              $("#" + relation["settings_selector_id"]).append(result);
            fetch();
          } else if (d["logic"] == "save") {
            fetchOptions();

            let createNewOption = true;
            $("#" + relation["selector_id"] + " option").each(function () {
              if ($(this).val() == result) {
                createNewOption = false;
              }
            });

            $(
              "#" + relation["selector_id"] + ' option[value="' + result + '"]'
            ).attr("selected", "selected");

            if (createNewOption) {
              successBox("New pattern successfully saved.");
            } else {
              successBox("Pattern successfully saved.");
            }
          } else if (d["logic"] == "remove") {
            fetchOptions();
            successBox("Pattern successfully deleted.");
          }
        },
      });
    }

    // fetch data from custom atrr
    function fetch() {
      let option = $("option:selected", $("#" + relation["selector_id"]));
      relation["fields"].forEach(function (field) {
        $("#" + field["key"]).val(option.attr(field["attr"]));
      });
    }

    // fetch data on option change
    $("#" + relation["selector_id"]).change(function () {
      fetch();
    });

    // create new option field
    $("#" + relation["new_button_id"]).click(function (e) {
      e.preventDefault();

      $("#" + relation["new_pattern_id"]).prop("selected", true);
      fetch();
    });

    // save option on button click
    $("#" + relation["save_button_id"]).click(function (e) {
      e.preventDefault();
      let id = $("#" + relation["selector_id"]).val();
      let send = true;

      if (id == "1") {
        alertBox("You can not edit default setting");
      } else {
        relation["fields"].forEach(function (field) {
          if (
            field["important"] == 1 &&
            $("#" + field["key"]).val() == "" &&
            send
          ) {
            send = false;
            alertBox(
              "Please enter all important data (name, email to reception and email to admin)!"
            );
            return 0;
          }
        });
        if (send) {
          let data = {};
          relation["fields"].forEach(function (field) {
            data[field["attr"]] = $("#" + field["key"]).val();
          });
          data["id"] = id;
          data["logic"] = "save";
          sendAjax(relation["ajax"]["url"], data);
        }
      }
    });

    // rm option on button click
    $("#" + relation["remove_email_id"]).click(function (e) {
      e.preventDefault();
      let id = $("#" + relation["selector_id"]).val();

      if (id == "1") {
        alertBox("You can not remove default setting");
      } else if (id == "") {
        alertBox("You can not remove pattern that was not saved!");
      } else {
        if (false) {
          alertBox("You can not remove active pattern!");
        }

        sendAjax(relation["ajax"]["url"], { logic: "remove", id: id });
      }
    });

    function fetchOptions() {
      // fill select tag with options from db
      sendAjax(relation["ajax"]["url"], { logic: "get" });
    }

    fetchOptions();
  }
  createCorrelation(email_pattern);
  createCorrelation(settings_pattern);

  $("#use_setting").click(function () {
    if ($("#platform_setting").val() == activePatterns["setting_pattern"]) {
      alertBox("This setting pattern already is use.");
    } else {
      $.ajax({
        type: "POST",
        url: settings_pattern["ajax"]["url"],
        data: {
          logic: "use",
          id: $("#platform_setting").val(),
        },
        success: function () {
          successBox("Pattern successfully moved in use.");
          activePatterns["setting_pattern"] = $("#platform_setting").val();
        },
      });
    }
  });
});
