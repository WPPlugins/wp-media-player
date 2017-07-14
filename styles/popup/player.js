///////////////////////////////////////////////////////////////////////////////
//
//  ExtendedPlayer
//
//  This extends the base player class, you may override the base player
//  member functions or add additional player functionality here. Here 
//  we monitor the mouse position and show popup player controls and chapter
//  controls if we hover near them.
//
///////////////////////////////////////////////////////////////////////////////
Type.registerNamespace('ExtendedPlayer');

ExtendedPlayer.Player = function(domElement) {
	ExtendedPlayer.Player.initializeBase(this, [domElement]);    
}
ExtendedPlayer.Player.prototype =  {
	onPluginLoaded: function(args) {    
		ExtendedPlayer.Player.callBaseMethod(this, 'onPluginLoaded', [args]);       
		this._controlsHotspot = new ExpressionPlayer.HotspotButton(this, "ControlsHotspot");            
		this._chapterHotspot = new ExpressionPlayer.HotspotButton(this, "ChapterHotspot");          
	},    

	numVisibleChapters: function (chaptersList) {
		var cVisibleChapters=0;
		if (chaptersList) {
			for (var i = 0, l = chaptersList.length; i < l; i++) {
				if (chaptersList[i].get_thumbnailSource()) 
					cVisibleChapters++;
			}
		}
		return cVisibleChapters;
	},
		
	set_chapters: function(value){
		ExtendedPlayer.Player.callBaseMethod(this, 'set_chapters', [value]);   
		this._chapterHotspot.get_element().Visibility =  (this.numVisibleChapters(value)>0)?0:1;   		
	},    

	pluginDispose: function() {
		if (this._controlsHotspot) {
			this._controlsHotspot.dispose();
		}    
		this._controlsHotspot=null;
		if (this._chapterHotspot) {
			this._chapterHotspot.dispose();
		}
		this._chapterHotspot=null;
		ExtendedPlayer.Player.callBaseMethod(this, 'pluginDispose');    		
	} 
}
ExtendedPlayer.Player.registerClass('ExtendedPlayer.Player', ExpressionPlayer.Player);
