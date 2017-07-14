///////////////////////////////////////////////////////////////////////////////
//
//  ExtendedPlayer
//
//  This extends the base player class, you may override the base player
//  member functions or add additional player functionality here. Here 
//  we add a button class that toggles a show/hide animation
//
///////////////////////////////////////////////////////////////////////////////
Type.registerNamespace('ExtendedPlayer');

ExtendedPlayer.Player = function(domElement) {
	ExtendedPlayer.Player.initializeBase(this, [domElement]);  
}
ExtendedPlayer.Player.prototype =  {
	onPluginLoaded: function(args) {    
		ExtendedPlayer.Player.callBaseMethod(this, 'onPluginLoaded', [args]);    
		this._showHideControlsButton = new ExpressionPlayer.ShowHideAnimationButton(this, "ToggleControlsButton", "Control", true);
	},

	pluginDispose: function() {
		if (this._showHideControlsButton) {
			this._showHideControlsButton.dispose();
	    }
		this._showHideControlsButton = null;
		ExtendedPlayer.Player.callBaseMethod(this, 'pluginDispose');
	}
}   
ExtendedPlayer.Player.registerClass('ExtendedPlayer.Player', ExpressionPlayer.Player);
