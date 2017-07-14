///////////////////////////////////////////////////////////////////////////////
//
//  ExtendedPlayer
//
//  This extends the base player class, you may override the base player
//  member functions or add additional player functionality here. Here 
//  we add additional control types to perform the expand and collapse
//  functionality for the Silverlight player.
//
///////////////////////////////////////////////////////////////////////////////
Type.registerNamespace('ExtendedPlayer');

ExtendedPlayer.Player = function(domElement) {
	ExtendedPlayer.Player.initializeBase(this, [domElement]);      
}
ExtendedPlayer.Player.prototype =  {
	onPluginLoaded: function(args) {    
		ExtendedPlayer.Player.callBaseMethod(this, 'onPluginLoaded', [args]);    
		this._volumeControls = new ExtendedPlayer.MouseOverControl(this.get_element(), "VolumeHolder");
		this._timelineControls = new ExtendedPlayer.MouseOverControl(this.get_element(), "TimelineHolder");        
		this._toggleControlsControl = new ExtendedPlayer.ToggleControlsControl(this.get_element());
	},
	
	pluginDispose: function() {
		if (this._volumeControls) this._volumeControls.dispose();
		if (this._timelineControls) this._timelineControls.dispose();
		if (this._toggleControlsControl) this._toggleControlsControl.dispose();
		this._volumeControls = null;
		this._timelineControls = null;
		this._toggleControlsControl = null;
		ExtendedPlayer.Player.callBaseMethod(this, 'pluginDispose');    				
	}     
}   
ExtendedPlayer.Player.registerClass('ExtendedPlayer.Player',ExpressionPlayer.Player);


ExtendedPlayer.MouseOverControl = function(host, nameElement) {
	// plays animations on mouse enter/leave
	this._element = host.content.findName(nameElement);
	this._t1 = this._element.addEventListener("mouseEnter", Function.createDelegate(this, this._mouseEnter));
	this._t2 = this._element.addEventListener("mouseLeave", Function.createDelegate(this, this._mouseLeave));
	this._enter = host.content.findName(nameElement + "_MouseEnter");
	this._leave = host.content.findName(nameElement + "_MouseLeave");
}
ExtendedPlayer.MouseOverControl.prototype = {
	dispose: function() {
		this._element.removeEventListener("mouseEnter", this._t1);
		this._element.removeEventListener("mouseLeave", this._t2);
		this._enter = null;
		this._leave = null;
		this._element = null;
	},
	_mouseEnter: function() {
		this._enter.begin();
	},
	_mouseLeave: function() {
		this._leave.begin();
	}
}
ExtendedPlayer.MouseOverControl.registerClass("ExtendedPlayer.MouseOverControl");


ExtendedPlayer.ToggleControlsControl = function(host){
	this._chapterArea = host.content.findName('ChapterArea');
	this._playerControls = host.content.findName('PlayerControls');
	this._sbShow = host.content.findName('PlayerControls_Show');
	this._sbHide = host.content.findName('PlayerControls_Hide');
	this._sbTimer = host.content.findName('PlayerControls_HideTimer');
	this._controlsVisible = false;
	
	this._t1 = this._sbTimer.addEventListener("Completed", Function.createDelegate(this, this._controlsAreaStartHide));
	this._t2 = this._sbHide.addEventListener("Completed", Function.createDelegate(this, this._controlsAreaHidden));
	this._t3 = this._sbShow.addEventListener("Completed", Function.createDelegate(this, this._controlsAreaShown));
	this._t4 = this._playerControls.addEventListener("mouseEnter", Function.createDelegate(this, this._mouseEnter));
	this._t5 = this._playerControls.addEventListener("mouseLeave", Function.createDelegate(this, this._mouseLeave));
}
ExtendedPlayer.ToggleControlsControl.prototype={ 
	_controlsAreaStartHide: function() {
		if (this._controlsVisible) this._sbHide.begin();
	},
	_controlsAreaShown: function() {
		this._controlsVisible = true;
	},
	_controlsAreaHidden: function() {
		this._controlsVisible = false;
		this._chapterArea.opacity = 0;
	},
	_mouseEnter: function() {
		this._sbTimer.stop();  
		if (!this._controlsVisible) {
			this._sbShow.begin();
			this._chapterArea.opacity = 0;           
		}            
	},
	_mouseLeave: function() {
		this._sbTimer.begin();
	},
	dispose: function() {
		this._sbTimer.removeEventListener("Completed", this._t1);
		this._sbHide.removeEventListener("Completed", this._t2);
		this._sbShow.removeEventListener("Completed", this._t3);
		this._playerControls.removeEventListener("mouseEnter", this._t4);
		this._playerControls.removeEventListener("mouseLeave", this._t5);
		this._chapterArea = null;
		this._playerControls = null;
		this._sbShow = null;
		this._sbHide = null;
		this._sbTimer = null;
	}
}
ExtendedPlayer.ToggleControlsControl.registerClass('ExtendedPlayer.ToggleControlsControl');
