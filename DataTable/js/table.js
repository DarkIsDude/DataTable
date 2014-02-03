$(document).ready(function() {
	$('table[dataTable]').dataTablePlus();
});

(function($) {
	$.fn.dataTablePlus = function(options) {
		// **************************
		// ****** INITIALISATION ****
		// **************************
		if (this.length > 1){
	        this.each(function() {
	        	$(this).dataTablePlus(options)
	        });
	        return this;
	    }
		
		var defauts = {};
		var parametres = $.extend(defauts, options);
		
		// **************************
		// ****** FUNCTION INIT *****
		// **************************		
		this.init = function () {
			var urlLanguage = this.attr("link") + "translations/" + this.attr("language") + ".json";
			var root = this;
			$.getJSON(urlLanguage, null, function(json) {
				
				root.dataTable({
					"bPaginate": true,
			        "bLengthChange": true,
			        "bFilter": true,
			        "bSort": true,
			        "bInfo": true,
			        "bAutoWidth": true,
			        "bJQueryUI": true,
			        "sPaginationType": "full_numbers",
			        "oLanguage": json,
			        "fnInitComplete": function(oSettings, jsonUseless) {
			        	this.name = this.attr("table");
			        	this.language = json;
			        	
						this.div = this.parent();
						this.modal = this.div.next();
						this.canUpdate = this.attr("update");
						this.canDelete = this.attr("delete");
						this.canCreate = this.attr("create");
						
						this.initParent();
						this.setBootstrap();
						
						if (this.canUpdate)
							this.updatable();
						if (this.canDelete)
							this.deletable();
						if (this.canCreate)
							this.creatable();
						
						// When I leave the page
						$(window).unload("beforeunload", function() {
							root.send($(this), {"id" : root.attr("identifier"), "function" : "removeSerialized"}, function(response) {}, false);
						});
			        }
			    });
			});
			bInitHandedOff = true;
		}

		this.initParent = function () {
			// Ajout de l'icone de chargement
			var pr = $("<img />").addClass("pull-right preloader").attr("src", this.attr("link") + "images/loading.gif").attr('id', 'preloader' + this.name);
			pr.attr("width", "20px");
			$(".fg-toolbar .dataTables_filter").after(pr);
			
			this.preloader = pr;
		}
		
		// **************************
		// ****** FUNCTION OTHER ****
		// **************************
		this.setBootstrap = function () {
			this.addClass('table table-hover table-bordered table-striped');
			
			this.div.find('div[class="dataTables_length"]').addClass('pull-left');
			this.div.find('select[aria-controls]').addClass('btn btn-default');
			
			var divFilter = this.div.find('div[class="dataTables_filter"]');
			var label = divFilter.find('label');
			var input = divFilter.find('input[aria-controls]');
			divFilter.addClass('pull-right form-group');
			input.addClass('form-control');
			
			this.css('clear', 'both');
			this.div.find('div[class="dataTables_info"]').addClass('dataTables_info pull-left');
			this.div.find('div[class="dataTables_paginate fg-buttonset ui-buttonset fg-buttonset-multi ui-buttonset-multi paging_full_numbers pagination pull-right"] a').each(function() {
				newLi = document.createElement("li");
				$(this).before(newLi);
				$(newLi).append($(this));
			});
		}
		
		this.showMessage = function (element, title, content) {
			element.popover({
				"offset": 10,
				"placement": "bottom",
				"trigger": "manual",
				"title": title,
				"content": content
			}).popover('show');
			
			window.setTimeout(function() {
				element.popover('hide');
		    }, 3000);
		}
		
		this.transformCase = function (th) {
			switch (th.attr("type")) {
				case ("boolean"):
					if (th.attr("value") == "1" || th.attr("value") == "true")
						th.empty().append($("<i />").addClass("glyphicon glyphicon-check"));
					else
						th.empty().append($("<i />").addClass("glyphicon glyphicon-unchecked"));
					break;
				case ("linked"):
	        	case ("number"):
	        	case ("double"):
	        	case ("varchar"):
	        	case ("text"):
	        	case ("date"):
	        	case ("datetime"):
	        	case ("time"):			                    	
	        	default:
	        		th.text(th.attr("value"));
	        		break;
			}
		}
		
		this.send = function (element, data, callback, async) {
			if (typeof async === "undefined" || async === null) 
				async = true;
			
			var root = this;
			this.preloader.css('visibility', 'visible');
			
			return $.ajax({
				  url: root.attr("link") + "php/ajax.php",
				  type: "POST",
				  data: data,
				  async: async,
				  dataType: "json"
			}).fail(function(jqXHR, textStatus) {
				root.showMessage(element, root.language.eFail + ": " + textStatus);
				root.preloader.css('visibility', 'visible');
			}).done(function(msg) {
				root.preloader.css('visibility', 'hidden');
				if (!msg.code && !msg.message)
					root.showMessage(element, root.language.eFail);
				else
					callback(msg);
			});	
		}
		
		this.getValue = function (input, type) {
			var value = "";
			
			switch (type) {
				case ("boolean"):
					if (input.attr("checked"))
						value = "1";
					else
						value = "0";
					break;
				case ("linked"):
	        	case ("number"):
	        	case ("double"):
	        	case ("varchar"):
	        	case ("text"):
	        	case ("date"):
	        	case ("datetime"):
	        	case ("time"):			                    	
	        	default:
	        		value = input.val();
	        		break;
			}
			
			return value;
		}
		
		this.setValue = function (input, type, value) {
			switch (type) {
				case ("boolean"):
	        		input.attr('checked', value != "0" && value != "false");
	        		break;
				case ("linked"):
	        		input.find("option").each(function() {
	        			if ($(this).text() == value)
	        				$(this).attr('selected', 'selected');
	        		});
	        		break;
				case ("text"):
	        	case ("number"):
	        	case ("double"):
	        	case ("varchar"):
	        	case ("date"):
	        	case ("datetime"):
	        	case ("time"):			                    	
	        	default:
	        		input.attr('value', value);
	        		break;
			}
		}
		
		// **************************
		// ****** FUNCTION UPDATE ***
		// **************************
		this.updatable = function () {
			if (this.canUpdate) {
				var root = this;
				
				this.$('tr').each(function() {
					$(this).find("th").each(function() {
						root.transformCase($(this));
						
						// Impossible d'éditer les index
						if ($(this).attr('index') != 'true') {
							$(this).bind('dblclick', function() {
								// Changement de l'affichage
								var th = $(this);
								var dataname = th.attr("dataname")
								var input = root.modal.find("*[dataname='" + dataname + "']").clone(false);
							    
								// Mise en place de la valeur
								root.setValue(input, th.attr("type"), th.attr("value"));
								
								// Mise en place de l'élément
								th.empty();
								th.append(input);
								input.focus();

								// Si je presse échape
								input.bind("keyup", function (e) {
									if (e.keyCode == 27) {
										th.empty();
										root.transformCase(th);
									}
								});
								
								// Si je perds le focus
								input.bind("focusout", function (e) {
									root.updateRow(th, input);
								});
							});
						}
					})
				})
			}
		};
		
		this.updateRow = function (th, input) {
			if (this.canUpdate) {
				var root = this;
				
				var tr = th.parent();
				var name = th.attr("dataname");
				var index = tr.attr("index");
				var oldValue = th.attr("value");
				var newValue = root.getValue(input, th.attr("type"));

				// Mise à jour
				var data = {
					  "id" : root.attr("identifier"),
					  "function" : "update",
					  "name" : name,
					  "index" : index,
					  "oldValue" : oldValue,
					  "newValue" : newValue
				}
				
				root.send(input, data, function(response) {
			    	if (response.success) {
				    	th.empty();
						th.attr("value", response.message);
						root.fnUpdate(response.message, tr[0], tr.children().index(th));
						root.transformCase(th);
			    	}
			    	else {
			    		root.showMessage(input, root.language.eFail, response.message);
			    	}
				});

			}
		}
		
		// **************************
		// ****** FUNCTION CREATE ***
		// **************************
		this.creatable = function () {
			if (this.canCreate) {
				var root = this;
				
				// Ajout de l'image pour ajout
				root.addButton = $("<button />").addClass("btn btn-default pull-right addLine");
				var icon = $("<i />").addClass("glyphicon glyphicon-plus");
				root.addButton.append(icon);
				$(".fg-toolbar .dataTables_filter").after(root.addButton);
				
				// Affichage de la modal
				root.addButton.bind("click", function() {
					root.modal.modal('show');
				});
				
				// Validation du formulaire
				var btn = this.modal.find(".btn-primary");
				
				btn.unbind();
				root.modal.find("*[typedata]").each(function () {
					root.setValue($(this), $(this).attr("typedata"), "");
				});
				
				btn.bind("click", function () {
					root.createRow();
				});	
			}
		}
		
		this.createRow = function () {
			this.modal.modal('hide');
			
			if (this.canCreate) {
				var root = this;

				// Récupération des valeurs
				var objetJSON = {};
				this.modal.find("*[dataname]").each(function () {
					objetJSON[$(this).attr("dataname")] = root.getValue($(this), $(this).attr("typeData"));
				});
				
				var data = {
					  "id" : root.attr("identifier"),
					  "function" : "create",
					  "json" : JSON.stringify(objetJSON),
				}
				
				root.send(root.addButton, data, function(response) {
			    	if (response.success) {
			    		root.div.after(response.message);
			    		var elem = root.div.next();
			    		
			    		root.div.remove();
			    		elem.dataTablePlus();
			    	}
			    	else {
			    		root.showMessage(root.addButton, root.language.eFail, response.message);
			    	}
				});
			}
		}
		
		// **************************
		// ****** FUNCTION DELETE ***
		// **************************
		this.deletable = function () {
			if (this.canDelete) {
				var root = this;
				
				// Ajout de l'image pour suppression
				var trash = $("<button />").addClass("btn btn-default pull-right removeLine");
				var icon = $("<i />").addClass("glyphicon glyphicon-trash");
				trash.append(icon);
				trash.attr("droppable", true);
				trash.attr("data-toggle", "tooltip");
				trash.attr("data-placement", "bottom");
				trash.attr("data-original-title", root.language.sDragDrop);
				trash.tooltip();
				$(".fg-toolbar .dataTables_filter").after(trash);
				
				// Application du draggable
				this.$('tr').each(function () {
					$(this).attr("draggable", true);
					
					$(this).bind("dragstart", function (e) {
						e.originalEvent.dataTransfer.setData("index", $(this).attr("index"));
					});
					
					$(this).bind("click", function (e) {
						if ($(this).hasClass("selected"))
							$(this).removeClass("selected");
						else
							$(this).addClass("selected");
					});
				});
				
				// Liaison des évnements
				trash.bind('dragenter', function(e) {
					e.stopPropagation();
			    	e.preventDefault();
					$(this).addClass("drag");
				});
				
			    trash.bind('dragover', function(e) {
			    	e.stopPropagation();
			    	e.preventDefault();
			    	$(this).addClass("drag");
			    });
			    
			    trash.bind('dragleave', function(e) {
			    	e.stopPropagation();
			    	e.preventDefault();
			    	$(this).removeClass("drag");
			    });
			    
			    trash.bind('drop', function(e) {
			    	e.stopPropagation();
			    	e.preventDefault();
			    	$(this).removeClass("drag");
			    	
			    	var index = e.originalEvent.dataTransfer.getData("index");
			    	var tr = root.find("tr[index='" + index + "']").each(function() {
			    		root.deleteRow($(this));
			    	});
			    });
			    
			    trash.bind('click', function(e) {
			    	root.$('tr.selected').each(function () {
			    		root.deleteRow($(this));
			    	});
			    });
			}
		}
		
		this.deleteRow = function (tr) {
			if (this.canDelete) {
				var root = this;
				var index = tr.attr("index");
				
				var data = {
					  "id" : root.attr("identifier"),
					  "function" : "delete",
					  "index" : index,
				}

				root.send(tr, data, function(response) {
			    	if (response.success) {
			    		root.fnDeleteRow(tr[0]);
			    	}
			    	else {
			    		root.showMessage(tr, root.language.eFail, response.message);
			    	}
				});
			}
		}
		
		// **************************
		// ****** EXECUTE ***********
		// **************************
		this.init();		
		return this;
	};
})(jQuery);