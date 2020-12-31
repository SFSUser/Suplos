let Finder = {
  callAction: function(action, success_call, error_call, complete_call){
    var data = {
        action: action
    };
    $.ajax({
      url: '/controller/mainController.php',
      data: data,
      success: success_call,
      complete: complete_call,
      error: error_call
    });
  },
  clear: function(){
    $("form").find("select, input").not("#submitButton").val("");
    this.search();
  },
  load: function(){
    var c = this;
    //Cargar ciudades
    this.callAction('getCityAction', function(r){
      var input = $("select[name=ciudad]");
      r.forEach(function(e){
        var option = document.createElement("option");
        option.value = e;
        option.innerText = e;
        input.append(option);
      });
    });

    //Cargar tipos
    this.callAction('getTypeAction', function(r){
      var input = $("select[name=tipo]");
      r.forEach(function(e){
        var option = document.createElement("option");
        option.value = e;
        option.innerText = e;
        input.append(option);
      });
    });

    $("#formulario").submit(function(e){
      e.preventDefault();
      c.search();
    });

    c.search();
  },
  saveItem(id){
    console.log("Guardando: " + id);
    if(!confirm("¿Confirmas que deseas guardar este elemento?")){
      return;
    }
    $.ajax({
        url: '/controller/mainController.php',
        data: {
          action: 'saveAction',
          id: id
        },
        success: function(r){
          if(r.result < 1){
            alert(r.message);
          } else {
            alert("Se ha guardado correctamente!");
          }
        },
        error: function(){
          alert("Ocurrió un error al intentar obtener los resultados de búsqueda.");
        }
    });
  },
  getItem(data){
    var c = this;
    var template = $("#bien_template").clone();
    template.show();
    template.attr("id", "");
    template.find(".action-save").click(function(){
      c.saveItem(data.Id);
    });
    for(var k in data){
      template.find("." + k).html(data[k]);
    }
    return template;
  },
  search: function(){
    var c = this;
    var container = $("#search_resultados");
    var ciudad = $("select[name=ciudad]").val();
    var tipo = $("select[name=tipo]").val();
    var precio = $("input[name=precio]").val();
    container.html("Buscando resultados....");
    $.ajax({
        url: '/controller/mainController.php',
        data: {
          action: 'searchAction',
          ciudad: ciudad,
          tipo: tipo,
          precio: precio
        },
        success: function(r){
          container.html("");
          r.forEach(function(e){
            var nuevo_bien_item = c.getItem(e);
            container.append(nuevo_bien_item);
          });
        },
        error: function(){
          alert("Ocurrió un error al intentar obtener los resultados de búsqueda.");
        }
    });
  },
  getSaved: function(){
    var c = this;
    var container = $("#search_resultados_guardados");
    container.html("Buscando guardados...");
    this.callAction("searchSavedAction", function(r){
      container.html("");
      r.forEach(function(d){
        var bien_guardado_item = c.getItem(d);
        bien_guardado_item.find(".action-save").remove();
        container.append(bien_guardado_item);
      });
    });
  }
};