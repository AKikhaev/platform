/*
 * Class:		multipleSelectFilter http://www.cbolson.com/sandbox/mootools/multipleSelectModalFilter/index.php
 * Authors:		Chris Bolson <chris[at]cbolson.com>, full rewrited a@kubado.ru
 * Requires: 	core 1.2.4 more: delegation
 *
 * Initiate: 	new multipleSelectFilter({options})
******************************/


var multipleSelectFilter = new Class({
	Implements: [Events, Options, Chain],
	options:{
		size:{x:500,y:250},
		txtInitial:'Фильтровать элементы...',
		txtBtClose:'Закрыть',
		txtBtFilterReset:'Показать все',
		txtBtListClear:'Снять выделение',
		imgClose:'assets/close.png'
	},
	
	// Initializes the modal class
	initialize:function(options){
		this.setOptions(options);
	},
	
	init:function(el){
		var selectList		= el;
		var selectOptions	= selectList.getElements('option');
		var title			= selectList.get('title');
		var selectBoxHeight	= (this.options.size.y-80);
		var txtInitial		= this.options.txtInitial;
		
		//	create array select list elements
		var listItems="";
		var filterOptions=selectOptions.each(function(option,index){	
			optionValue=option.get('value').trim();
			if(optionValue!=""){
				var optionClasses='';
				var optionText	=option.get('text');
				
				//	check if option is selected - add class
				if(option.get('selected')) 	optionClasses+=' checked';
				
				//	check if option has special class (used if the basic select list has special classes - these classes must be added to the style sheet
				if(option.get('class')) 	optionClasses+=' '+option.get('class')+'';
				
				//	add option to unordered list
				listItems += '<li id="opt_'+index+'" rel="'+optionValue+'" class="'+optionClasses+'">'+optionText+'</li>';
			}
		});		
		
		//	CREATE FILTER - input, buttons and list
		//	use html string method to optimize for ie6
		var filterHTML='';
		filterHTML+='<input type="text" id="searchbox" class="search" value="'+txtInitial+'">';
		filterHTML+='<input type="button" id="btListClear" class="btFilter" value="'+this.options.txtBtListClear+'">';
		filterHTML+='<input type="button" id="btListReset" class="btFilter" value="'+this.options.txtBtFilterReset+'">';
		//filterHTML+='<input type="button" id="btSave" class="btFilter" value="'+this.options.txtBtClose+'">';
		filterHTML+='<ul style="height:'+selectBoxHeight+'px;">'+listItems+'</ul>';
		
		var filterWrapper = new Element('div',{
			'class':'filterWrapper',
			'html':filterHTML
		});
		
		//	define elements
		var filterList		= filterWrapper.getElement('ul');
		var filterListItems = filterList.getElements('li').setStyle('cursor','pointer');
		//	store item index,value and text
		filterListItems.each(function(el){
			el.store('oTxtValue',el.get('html').toLowerCase()).store('oValue',el.get('rel')).store('oIndex',el.id.replace('opt_',''));
	    });
		
		var filterTextbox	= filterWrapper.getFirst('input').addEvents({
			'focus':function(el){
				if(this.value==''+txtInitial+''){ this.value="";}
			},
			'keyup':function(){
				checkListMatches(this.value);
			}
		});
		
		//	reset filter
		var btReset = filterWrapper.getElement('input[id=btListReset]').addEvent('click',function(){
			filterListReset();
		}).setStyle('opacity',0);
		
		//	unselect all
		var btClear = filterWrapper.getElement('input[id=btListClear]').addEvent('click',function(){
		    filterClearList();
			//filterListItems[0].addClass('checked');
			//selectOptions[0].setProperty('selected', true);
		});
		
		//	set reset button opacity
		var btResetToggle = function(opc){
			btReset.setStyle('opacity',opc);
		}
		
		//	filter for matches
		var checkListMatches = function(str){
	        //	toggle reset button
	        if(str.length==0) btResetToggle(0);
	        else btResetToggle(1);
	        
	        str=str.toLowerCase();
	        filterListItems.each(function(el){
	            (el.retrieve('oTxtValue').contains(str)) ? el.setStyle('display','block') :el.setStyle('display','none');
	        });
	    };
	    
	    //	reset filter list
	    var filterListReset = function(){
	    	 filterListItems.each(function(el){
	            el.setStyle('display','block');
	        });
	        filterTextbox.set('value','');
	        btResetToggle(0);
	    }
	 	
	 	//	clear selected items
	    var filterClearList=function(){
	    	filterListItems.removeClass('checked');	 
			selectList.getSelected().setProperty('selected', false);
	       	filterListReset();
	    }
	    
		//	add events for li items via delegation method
		filterList.addEvents({
			'click:relay(li)':function(){
				//	define id of item clicked
				index=filterListItems.indexOf(this);
				optionID=filterListItems[index].retrieve('oIndex');
				
				if(filterListItems[index].hasClass('checked')){
					filterListItems[index].removeClass('checked');	
					selectOptions[optionID].setProperty('selected', false);
				}else{
					filterListItems[index].addClass('checked');
					selectOptions[optionID].setProperty('selected', true);
				}
			},
			'mouseover:relay(li)':function(event){
				if(event.shift){
					index=filterListItems.indexOf(this);
					optionID=filterListItems[index].retrieve('oIndex');
					if(filterListItems[index].hasClass('checked')){
			  			filterListItems[index].removeClass('checked');
			  			selectOptions[optionID].setProperty('selected', false);
			  		}else{
			   			filterListItems[index].addClass('checked');
			   			selectOptions[optionID].setProperty('selected', true);
			   		}
				}else{
					this.highlight('#FFCC00',this.getStyle('backgroundColor'));
			   	}
			}
			
		});

		filterWrapper.inject(el.addClass('hidden'),'after');
		
		filterTextbox.focus();	
	}
});