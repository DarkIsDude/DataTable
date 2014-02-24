$(document).ready(function() {
	// Add natural sort
	function naturalSort (a, b) {
		if(a=='-' && b != '-') return -1;
		if(a=='-' && b == '-') return 0;
		if(b=='-' && a != '-') return 1;
	    var re = /(^-?[0-9]+(\.?[0-9]*)[df]?e?[0-9]?$|^0x[0-9a-f]+$|[0-9]+)/gi,
	        sre = /(^[ ]*|[ ]*$)/g,
	        dre = /(^([\w ]+,?[\w ]+)?[\w ]+,?[\w ]+\d+:\d+(:\d+)?[\w ]?|^\d{1,4}[\/\-]\d{1,4}[\/\-]\d{1,4}|^\w+, \w+ \d+, \d{4})/,
	        hre = /^0x[0-9a-f]+$/i,
	        ore = /^0/,
	        // convert all to strings and trim()
	        x = a.toString().replace(sre, '') || '',
	        y = b.toString().replace(sre, '') || '',
	        // chunk/tokenize
	        xN = x.replace(re, '\0$1\0').replace(/\0$/,'').replace(/^\0/,'').split('\0'),
	        yN = y.replace(re, '\0$1\0').replace(/\0$/,'').replace(/^\0/,'').split('\0'),
	        // numeric, hex or date detection
	        xD = parseInt(x.match(hre)) || (xN.length != 1 && x.match(dre) && Date.parse(x)),
	        yD = parseInt(y.match(hre)) || xD && y.match(dre) && Date.parse(y) || null;
	    // first try and sort Hex codes or Dates
	    if (yD)
	        if ( xD < yD ) return -1;
	        else if ( xD > yD )  return 1;
	    // natural sorting through split numeric strings and default strings
	    for(var cLoc=0, numS=Math.max(xN.length, yN.length); cLoc < numS; cLoc++) {
	        // find floats not starting with '0', string or 0 if not defined (Clint Priest)
	        var oFxNcL = !(xN[cLoc] || '').match(ore) && parseFloat(xN[cLoc]) || xN[cLoc] || 0;
	        var oFyNcL = !(yN[cLoc] || '').match(ore) && parseFloat(yN[cLoc]) || yN[cLoc] || 0;
	        // handle numeric vs string comparison - number < string - (Kyle Adams)
	        if (isNaN(oFxNcL) !== isNaN(oFyNcL)) return (isNaN(oFxNcL)) ? 1 : -1;
	        // rely on string comparison if different types - i.e. '02' < 2 != '02' < '2'
	        else if (typeof oFxNcL !== typeof oFyNcL) {
	            oFxNcL += '';
	            oFyNcL += '';
	        }
	        if (oFxNcL < oFyNcL) return -1;
	        if (oFxNcL > oFyNcL) return 1;
	    }
	    return 0;
	}
	 
	jQuery.extend(jQuery.fn.dataTableExt.oSort, {
	    "natural-asc": function ( a, b ) {
	        return naturalSort(a,b);
	    },
	    "natural-desc": function ( a, b ) {
	        return naturalSort(a,b) * -1;
	    }
	} );
	
	
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
		
		// Init everithing
		this.init = function () {
			var root = this;
			var options = {
		        "bLengthChange": true,
		        "bInfo": true,
		        "bAutoWidth": true,
		        "bJQueryUI": true,
		        "bStateSave": true,
		        "bFilter": true
			};
			
			// When I leave the page
			$(window).unload("beforeunload", function() {
				root.send($(this), {"id" : root.attr("identifier"), "function" : "removeSerialized"}, function(response) {}, false);
			});
			
			// For pagination
			options["bPaginate"] = true;
			if (root.attr("pagination"))
				options["sPaginationType"] = root.attr("pagination");
			else
				options["bPaginate"] = false;
			
			// Sort
			options["bSort"] = root.attr("sortable");
			if (options["bSort"] == "natural") {
				options["aoColumnDefs"] = new Array();
				var i = 0;
				this.find('th').each(function() {
					options["aoColumnDefs"][i] = {
						"sType" : "natural",
						"aTargets" : [i]
					};
					i++;
				})
			}
			
			// End function
			options["fnInitComplete"] = function(oSettings, jsonUseless) {
	        	this.name = this.attr("table");
	        	
				this.div = this.parent();
				this.modal = this.div.next();
				this.canUpdate = this.attr("update");
				this.canDelete = this.attr("delete");
				this.canCreate = this.attr("create");
				this.canExtension = this.attr("extension")
				
				this.initParent();
				this.initDragDrop();
				this.setBootstrap();
				
				if (this.canUpdate)
					this.updatable();
				if (this.canDelete)
					this.deletable();
				if (this.canCreate)
					this.creatable();
				if (this.canExtension)
					this.extension();
			};
			
			// Language
			var urlLanguage = this.attr("link") + "translations/" + this.attr("language") + ".json";
			
			// Launch
			$.getJSON(urlLanguage, null, function(json) {
				root.language = json;
				root.dataTable(options);
			});
			bInitHandedOff = true;
		}

		// Init the div
		this.initParent = function () {
			// Ajout de l'icone de chargement
			var pr = $("<img />").addClass("pull-right preloader").attr("src", this.attr("link") + "images/loading.gif").attr('id', 'preloader' + this.name);
			pr.attr("width", "20px");
			this.div.find(".fg-toolbar .dataTables_filter").after(pr);
			
			this.preloader = pr;
		}
		
		this.initDragDrop = function () {
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
		}
		
		// **************************
		// ****** FUNCTION OTHER ****
		// **************************
		
		// Set bootstrap style
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
		
		// Display a message (thanks to bootstrap) on this element
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
		
		// Display the correct value in the td (thanks to the value attribute)
		this.transformCase = function (td) {
			switch (td.attr("type")) {
				case ("boolean"):
					if (td.attr("value") == "1" || td.attr("value") == "true")
						td.empty().append($("<i />").addClass("glyphicon glyphicon-check"));
					else
						td.empty().append($("<i />").addClass("glyphicon glyphicon-unchecked"));
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
	        		td.text(td.attr("value"));
	        		break;
			}
		}
		
		// Send to ajax.php a query
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
		
		// Get value of the input
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
		
		// Set value of input
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
					$(this).find("td").each(function() {
						root.transformCase($(this));
						
						// Impossible d'éditer les index
						if ($(this).attr('index') != 'true') {
							$(this).bind('dblclick', function() {
								// Changement de l'affichage
								var td = $(this);
								var dataname = td.attr("dataname")
								var input = root.modal.find("*[dataname='" + dataname + "']").clone(false);
							    
								// Mise en place de la valeur
								root.setValue(input, td.attr("type"), td.attr("value"));
								
								// Mise en place de l'élément
								td.empty();
								td.append(input);
								input.focus();

								// Si je presse échape
								input.bind("keyup", function (e) {
									if (e.keyCode == 27) {
										td.empty();
										root.transformCase(td);
									}
								});
								
								// Si je perds le focus
								input.bind("focusout", function (e) {
									root.updateRow(td, input);
								});
							});
						}
					})
				})
			}
		};
		
		this.updateRow = function (td, input) {
			if (this.canUpdate) {
				var root = this;
				
				var tr = td.parent();
				var name = td.attr("dataname");
				var index = tr.attr("index");
				var oldValue = td.attr("value");
				var newValue = root.getValue(input, td.attr("type"));

				// Mise à jour
				var data = {
					  "id" : root.attr("identifier"),
					  "function" : "update",
					  "name" : name,
					  "index" : index,
					  "newValue" : newValue
				}
				
				root.send(input, data, function(response) {
			    	if (response.success) {
				    	td.empty();
						td.attr("value", response.message);
						root.fnUpdate(response.message, tr[0], tr.children().index(td));
						root.transformCase(td);
						root.trigger("fnUpdate", td);
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
				this.div.find(".fg-toolbar .dataTables_filter").after(root.addButton);
				
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
			    		root.trigger("fnAdd", objetJSON);
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
				this.div.find(".fg-toolbar .dataTables_filter").after(trash);
				
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
			    		root.trigger("fnRemove", index);
			    	}
			    	else {
			    		root.showMessage(tr, root.language.eFail, response.message);
			    	}
				});
			}
		}
		
		// *****************************
		// ****** FUNCTION EXTENSION ***
		// *****************************
		this.extension = function() {
			if (this.canExtension) {
				var root = this;
				
				// Ajout de l'image pour suppression
				var option = $("<button />").addClass("btn btn-default pull-right optionLine");
				var icon = $("<i />").addClass("glyphicon glyphicon-new-window");
				option.append(icon);
				option.attr("droppable", true);
				option.attr("data-toggle", "tooltip");
				option.attr("data-placement", "bottom");
				option.attr("data-original-title", root.language.sDragDropOption);
				option.tooltip();
				this.div.find(".fg-toolbar .dataTables_filter").after(option);
				
				// Liaison des évnements
				option.bind('dragenter', function(e) {
					e.stopPropagation();
			    	e.preventDefault();
					$(this).addClass("drag");
				});
				
				option.bind('dragover', function(e) {
			    	e.stopPropagation();
			    	e.preventDefault();
			    	$(this).addClass("drag");
			    });
			    
				option.bind('dragleave', function(e) {
			    	e.stopPropagation();
			    	e.preventDefault();
			    	$(this).removeClass("drag");
			    });
			    
				option.bind('drop', function(e) {
			    	e.stopPropagation();
			    	e.preventDefault();
			    	$(this).removeClass("drag");
			    	
			    	var index = e.originalEvent.dataTransfer.getData("index");
			    	var tr = root.find("tr[index='" + index + "']").each(function() {
			    		root.trigger("fnExtension", $(this));
			    	});
			    });
			    
				option.bind('click', function(e) {
			    	root.$('tr.selected').each(function () {
			    		root.trigger("fnExtension", $(this));
			    	});
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