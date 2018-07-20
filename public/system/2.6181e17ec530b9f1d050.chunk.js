webpackJsonp([2],{1006:function(e,t){e.exports='<div class="page-detail-wrapper" [ngClass]="{ \'edit-mode\': _editMode }" [hidden]="_pageDetailItemList.length === 0">\r\n\t<div class="page-item-list-wrapper" *ngIf="_pageDetailItemList.length > 0">\r\n\t\t<div class="page-item" *ngFor="let item of _pageDetailItemList; let isLast = last" for-end [isLast]="isLast" (onForEnd)="initPlugin($event, idx)"\r\n            [attr.data-item-id]="item.id">\r\n\t\t\t<h4 class="title"><i class="material-icons page-item-using-global-item" *ngIf="item.global_item_id" bs-plugin [pluginList]="\'tooltip\'" [attr.title]="\'Linked up with \' + item.global_item.name" data-placement="top">public</i>{{ item.name }}</h4>\r\n\t\t\t<span class="action-button-wrapper">\r\n\t\t\t\t<span class="action-button" (click)="openEditMode($event, item)" bs-plugin [pluginList]="\'tooltip\'" [attr.title]="(item.global_item_id ? \'common.edit_global_item\' : \'common.edit\') | translate" data-placement="top">\r\n\t\t\t\t\t<i class="material-icons">mode_edit</i>\r\n\t\t\t\t</span>\r\n\t\t\t</span>\r\n\t\t</div>\r\n\t</div>\r\n</div>\r\n<span class="empty-table-message" *ngIf="_pageDetailItemList.length === 0" [hidden]="isBusy">{{ \'message.no_page_item\' | translate }}</span>\r\n\r\n<div class="go-back-btn" [ngClass]="{ \'edit-mode\': _editMode }" (click)="closeEditMode()">\r\n\t<i class="material-icons">keyboard_arrow_left</i>\r\n</div>\r\n\r\n<div class="page-detail-edit-mode" [ngClass]="{ \'edit-mode\': _editMode }">\r\n\t<cmp-content-translation-editor (onReady)="onEditModeReady()" (onComponentWorking)="isBusy = $event;" (onDataSaved)="closeEditMode()"></cmp-content-translation-editor>\r\n</div>\r\n\r\n<div class="component-busy" [hidden]="!isBusy" >\r\n\t<span input-loader [size]="\'32px\'"></span>\r\n</div>'},1007:function(e,t){e.exports='<div class="page-filter-box" [ngClass]="{\'showing-child\': selectedPageObj.id}">\r\n\t<div *ngIf="selectedPageObj.id" class="back-to-parent"\r\n\t\t(click)="backToParent()"\r\n\t\tbs-plugin [pluginList]="\'tooltip\'" [attr.title]="\'misc.go_back\' | translate" data-placement="top">\r\n\t\t<i class="material-icons">chevron_left</i>\r\n\t</div>\r\n\t<div class="search-box no-mgn-bottom">\r\n\t\t<input type="search" class="form-control" name="filter_page" [(ngModel)]="filterPageName" placeholder="{{ \'common.filter_page_content\' | translate }}" />\r\n\t</div>\r\n</div>\r\n\r\n<div class="page-filter-box page-category-filter-box" *ngIf="pageCategoryList.length > 0">\r\n\t<div class="search-box no-mgn-bottom form-group">\r\n\t\t<label for="page-category-filter">{{ \'common.filter_page_by_category_label\' | translate }}: </label>\r\n\t\t<select id="page-category-filter" class="form-control" name="filter_page_category" \r\n\t\t\t[(ngModel)]="filterPageCategory">\r\n\t\t\t<option value="">{{ \'common.filter_all\' | translate }}</option>\r\n\t\t\t<option *ngFor="let category of pageCategoryList" [value]="category">{{ category }}</option>\r\n\t\t</select>\r\n\t</div>\r\n</div>\r\n\r\n<div class="page-list-wrapper" [ngClass]="{ \'hidden\': isBusy }">\t\r\n\t<div class="page-item" *ngFor="let page of pageList | myPageListFilter: filterPageName: filterPageCategory: selectedPageObj.id" >\r\n\t\t<div (click)="openPageDetail($event, page)">\r\n\t\t\t<i class="material-icons page-image-icon">web</i>\r\n\t\t\t<span class="page-item-title">\r\n\t\t\t\t<label>{{ page.name }}</label> \r\n\t\t\t\t<small class="template-name">{{ page.template.name }}</small>\r\n\t\t\t</span>\r\n\t\t</div>\r\n\t\t\x3c!-- <i class="material-icons page-item-edit" bs-plugin [pluginList]="\'tooltip\'" [attr.title]="\'common.edit\' | translate">create</i> --\x3e\r\n\t\t<div class="page-item-action">\r\n\t\t\t<a class="table-item-icon" role="view"aria-function="view-child-item" (click)="openPageProperties($event, page)"><i class="material-icons">pageview</i></a>\r\n\t\t\t<a class="table-item-icon" role="edit" aria-function="edit-item" (click)="openPageDetail($event, page)"><i class="material-icons">edit</i></a>\r\n\t\t</div>\r\n\t\t<span class="page-item-view-sub" (click)="viewSubPage($event, page)" *ngIf="page.children && page.children.length > 0"\r\n\t\t\tbs-plugin [pluginList]="\'tooltip\'" [attr.title]="\'misc.view_child_page\' | translate: { childCount: page.children.length }">\r\n\t\t\t{{ page.children.length > 99 ? \'99+\' : page.children.length }}\r\n\t\t</span>\r\n\t</div>\r\n\t<div *ngIf="!isBusy && loadComplete && (pageList | myPageListFilter: filterPageName: filterPageCategory: selectedPageObj.id).length === 0" [innerHTML]="\'common.no_item_matched\' | translate"></div>\r\n</div>\r\n\r\n<div class="component-busy" [hidden]="!isBusy" >\r\n\t<span input-loader [size]="\'32px\'"></span>\r\n</div>'},942:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var i=a(986);a.d(t,"PagesContentModule",(function(){return i.a}))},951:function(e,t,a){"use strict";var i=a(19),n=a(26),o=a(12),s=a(35);a.d(t,"a",(function(){return r}));var r=(function(){function e(e){this._inj=e,this._pageTitle="",this._siteList=[],this._reqSvc=this._inj.get(i.a),this._router=this._inj.get(n.c),this._cmn=this._inj.get(o.a),this._dlg=this._inj.get(s.a)}return e.prototype.ngOnInit=function(){this._cmn.setInfo({navList:[]})},e.prototype.ngOnDestroy=function(){},e})()},952:function(e,t){e.exports='<div class="cmp-container">\r\n\t<router-outlet></router-outlet>\r\n</div>\r\n'},954:function(e,t,a){"use strict";var i=a(4),n=a(2),o=a(26),s=a(16),r=a(9),l=a(73),p=a(91),c=a(92),d=a(93),g=a(423),m=a(168);a.d(t,"a",(function(){return u}));var u=(function(e){function t(t,a,i,n,o){var s=e.call(this,t)||this;s._acRoute=a,s._router=i,s.el=n,s._zone=o,s.siteLanguages=[],s._pageDetailItemList=[],s._editMode=!1,s.isBusy=!0,s._pageItemDetail=[],s.itemElementOption={textEditorOptions:Object.assign({},r.m.contentTextEditorOptions,{height:400})},s.currentLangName="",s.currentLang="",s._currentSiteId="",s._originalData="",s._orderChanged=!1,s._pageId="",s.selectedPageItemId="",s._showHiddenValue=!1,s._elem.addClass("component-wrapper");var l=s._cmn.getComponentFactory(["[cmp-property-editor]","[cmp-control-list-manager]","[cmp-page-preview]"]);return l["[cmp-property-editor]"]?s._propEditCmpFactory=l["[cmp-property-editor]"]:(s._propEditCmpFactory=s._resolver.resolveComponentFactory(d.a),s._dlg.addCmpFactory(s._propEditCmpFactory)),l["[cmp-control-list-manager]"]?s._ctlListManagerCmpFactory=l["[cmp-control-list-manager]"]:(s._ctlListManagerCmpFactory=s._resolver.resolveComponentFactory(c.a),s._dlg.addCmpFactory(s._ctlListManagerCmpFactory)),l["[cmp-page-preview]"]?s._pagePreviewCmpFactory=l["[cmp-page-preview]"]:(s._pagePreviewCmpFactory=s._resolver.resolveComponentFactory(m.a),s._dlg.addCmpFactory(s._pagePreviewCmpFactory)),s._cmn.setInfo({navList:[{nav_text:"app_nav.pages_content",nav_link:"/page-content"},{nav_text:"app_nav.pages_content_detail",active:!0}],pageTitle:"app_nav.pages_content_detail"}),s.selectedPageItemId=window.sessionStorage.getItem("tmp-selected-id")||"",window.sessionStorage.removeItem("tmp-selected-id"),s._infoSubscription=s._cmn.getInfo().subscribe((function(e){e.currentSiteId!==s._currentSiteId&&(s._currentSiteId=e.currentSiteId,s.contentTransEditorCmp.setSiteId(s._currentSiteId),s.getSiteLanguages(),s.loadData()),s._showHiddenValue=[r.c.DEVELOPER].indexOf(e.currentUserLevel)>-1})),s}return i.a(t,e),t.prototype.ngOnInit=function(){this._elem.closest(".cmp-container").addClass("full-expand")},t.prototype.ngOnDestroy=function(){this._infoSubscription.unsubscribe()},t.prototype.loadData=function(){var e=this;this._acRoute.params.subscribe((function(t){if(t.id){e._pageId=t.id;var a=Object.assign({},s.a.url.page.getItemsById);a.url=a.url.replace(/(:id)/g,e._pageId),e._req.doRequest(a).subscribe((function(t){var a=Object.assign([],t.data);if(e._pageDetailItemList=a.filter((function(e){return e.is_active})),e._showHiddenValue||(e._pageDetailItemList=e._pageDetailItemList.filter((function(e){return e.is_visible}))),e._originalData=JSON.stringify(e._pageDetailItemList.map((function(e){return{id:e.id,display_order:e.display_order}}))),e.isBusy=!1,e.selectedPageItemId){var i=e._pageDetailItemList.filter((function(t){return t.id===e.selectedPageItemId}));i.length&&e.openEditMode(null,i[0])}})),e._pageId=t.id;var i=Object.assign({},s.a.url.page.getById);i.url=i.url.replace(/(:id)/g,e._pageId),e._req.doRequest(i).subscribe((function(t){t.data&&(e._pageData=t.data,e.contentTransEditorCmp.setPageData(e._pageData),e._cmn.setInfo({pageTitle:t.data.name,navList:[{nav_text:"app_nav.pages_content",nav_link:"/page-content"},{nav_text:"app_nav.pages_content_detail",active:!0}],topBarButtonPosition:"right",topBarButtonList:[{btnText:"button.preview",btnIcon:"visibility",btnClass:"btn-secondary",onClick:function(){e.previewPage()}},{btnText:"common.save",btnIcon:"save",btnClass:"btn-primary",disabled:!0}]}))}))}}))},t.prototype.getSiteLanguages=function(){var e=this;this._cmn.getSiteLanguages(this._currentSiteId).subscribe((function(t){e.siteLanguages=[],t.map((function(t){t.is_active&&e.siteLanguages.push({name:t.name,code:t.code,main:t.pivot.is_main||!1,current:!1})})),e.currentLang=e.siteLanguages[0].code,e.currentLangName=e.siteLanguages[0].name,e.selectedPageItemId}),(function(){e.siteLanguages=[{name:"English",code:"en",main:!0,current:!0}]}))},t.prototype.initPlugin=function(){var e=this;$(".page-item-list-wrapper").sortable({placeholder:"page-item-placeholder",cursorAt:{left:5,top:5},stop:function(t,a){e._zone.run((function(){e._pageDetailItemList.map((function(t){t.display_order=e._elem.find('.page-item-list-wrapper .page-item[data-item-id="'+t.id+'"]').index()+1})),e._pageDetailItemList.sort((function(e,t){return e.display_order<t.display_order?-1:1}));var t=JSON.stringify(e._pageDetailItemList.map((function(e){return{id:e.id,display_order:e.display_order}})));e._orderChanged=t!==e._originalData,e.validateSaveButtonState()}))}})},t.prototype.openEditMode=function(e,t){var a=this;this.isBusy=!0;var i={message:"message.get_data_failed",type:r.k.ERROR};if(t.hasOwnProperty("global_item_id")&&t.global_item_id){var n=t.global_item_id,o=Object.assign({},s.a.url.globalItem.getById);return o.url=o.url.replace(/(:id)/,n),this.isBusy=!1,this._cmn.showAppLoader(),this._req.doRequest(o).subscribe((function(e){if(a._cmn.hideAppLoader(),e.result){if(e.data.is_active){var i={title:{key:"dialog.redirect_to_global_item_title"},expandableDialog:!0,dialogCssClass:"size-md",useCustomButtons:!1,noMaximize:!0,template:{key:"message.redirect_to_global_item_from_page_item_config",param:{global:t.global_item.name}},buttons:[{text:{key:"common.yes"},cssClass:"btn-primary",materialIcon:"done",closeWhenClick:!0,activateClickAfterClose:!0,onClick:function(){setTimeout((function(){var e=a._acRoute._routerState.snapshot.url;window.sessionStorage.setItem("ignore-navigation-cancel","true"),window.sessionStorage.setItem("tmp-selected-id",t.global_item_id),window.sessionStorage.setItem("tmp-back-to-page-id",t.page_id),"/global-content"===e?a._cmn.setInfo({customTrigger:"GLOBAL_ITEM"}):a._router.navigate(["/global-content"])}),200)}},{text:{key:"common.cancel"},cssClass:"btn-secondary",materialIcon:"close",closeWhenClick:!0}]};a._dlg.open(i)}else{var i={title:{key:"dialog.redirect_to_global_item_inactive_title"},expandableDialog:!0,dialogCssClass:"size-md",useCustomButtons:!1,noMaximize:!0,template:{key:"message.global_item_is_inactive",param:{global:"("+t.global_item.name+")"}}};a._dlg.open(i)}}else{var i={title:{key:"dialog.redirect_to_global_item_not_found_title"},expandableDialog:!0,dialogCssClass:"size-md",useCustomButtons:!1,noMaximize:!0,template:{key:"message.global_item_not_found"}};a._dlg.open(i)}})),!0}var l=Object.assign({},s.a.url.pageItem.getOptionsById);l.url=l.url.replace(/(:id)/g,t.id),this._req.doRequest(l).subscribe((function(e){a._pageItemDetail=Object.assign([],e.data),a.contentTransEditorCmp.setPageItemData(t),a._pageItemDetail.length>0?(a._cmn.setInfo({navList:[{nav_text:"app_nav.pages_content",nav_link:"/page-content"},{nav_text:"app_nav.pages_content_detail_name",translateParam:{pageItemName:t.name},active:!0}]}),a.contentTransEditorCmp.initComponent({propertyList:a._pageItemDetail,moduleType:"page",siteLanguages:a.siteLanguages})):(i.message="message.no_page_item_options",i.type=r.k.NORMAL,a._sncBar.open(i),a.isBusy=!1)}),(function(){a.isBusy=!1,a._pageItemDetail=[],a._sncBar.open(i),a._cmn.setInfo({navList:[],pageTitle:"app_nav.global_item"})}))},t.prototype.onEditModeReady=function(){$(".component-wrapper").scrollTop(0),this._editMode=!0,this.isBusy=!1,setTimeout((function(){$(".page-detail-wrapper").addClass("hidden")}),500)},t.prototype.closeEditMode=function(){var e=this;$(".page-detail-wrapper").removeClass("hidden"),setTimeout((function(){if(e._editMode=!1,e._pageItemDetail=null,e._cmn.setInfo({navList:[{nav_text:"app_nav.pages_content",nav_link:"/page-content"},{nav_text:"app_nav.pages_content_detail",active:!0}]}),e.validateSaveButtonState(),window.sessionStorage.getItem("tmp-selected-id")||!1){var t=e._acRoute._routerState.snapshot.url;window.sessionStorage.setItem("ignore-navigation-cancel","true"),window.sessionStorage.setItem("tmp-back-to-page-id",e._pageId),"/global-content"===t?e._cmn.setInfo({customTrigger:"GLOBAL_ITEM"}):e._router.navigate(["/global-content"])}}),10)},t.prototype.saveReorderData=function(){var e=this;if(this._pageId){var t=Object.assign({},s.a.url.page.reorderItem),a={message:"message.item_removed",type:r.k.NORMAL},i=this._pageDetailItemList.map((function(e){return{id:e.id,display_order:e.display_order}}));t.url=t.url.replace(/(:id)/g,this._pageId),this.isBusy=!0,this._req.doRequest(t,{contentType:r.a.FORM,postData:{data:i}}).subscribe((function(t){e.isBusy=!1,t.result?(a.message="message.list_reordered",e._orderChanged=!1,e._originalData=JSON.stringify(e._pageDetailItemList.map((function(e){return{id:e.id,display_order:e.display_order}}))),e.validateSaveButtonState()):(a.message="message.list_reordering_failed",a.type=r.k.ERROR),e._sncBar.open(a)}),(function(){e.isBusy=!1,a.message="message.list_reordering_failed",a.type=r.k.ERROR,e._sncBar.open(a)}))}},t.prototype.validateSaveButtonState=function(){var e=this;this._orderChanged?this._cmn.setInfo({topBarButtonList:[{btnText:"button.preview",btnIcon:"visibility",btnClass:"btn-secondary",onClick:function(){e.previewPage()}},{btnText:"common.save",btnIcon:"save",btnClass:"btn-primary",onClick:function(){e.saveReorderData()}}]}):this._cmn.setInfo({topBarButtonList:[{btnText:"button.preview",btnIcon:"visibility",btnClass:"btn-secondary",onClick:function(){e.previewPage()}},{btnText:"common.save",btnIcon:"save",btnClass:"btn-primary",disabled:!0}]})},t.prototype.previewPage=function(){var e=this,t={};t.friendly_url=this._pageData.friendly_url,t.preview_data={},t.preview_data.page_data={},t.preview_data.global_data={},this._pageDetailItemList.map((function(e){t.preview_data.page_data[e.variable_name]={_display_order:e.display_order}})),this._cmn.showAppLoader(),this._cmn.previewPage(this._currentSiteId,t,this._pagePreviewCmpFactory).subscribe((function(t){e._cmn.hideAppLoader()}))},i.b([a.i(n._16)(l.a),i.c("design:type",n._17)],t.prototype,"diTextEditor",void 0),i.b([a.i(n._15)(g.a),i.c("design:type",g.a)],t.prototype,"contentTransEditorCmp",void 0),t=i.b([a.i(n._14)({selector:"[cmp-pages-content-detail]",template:a(1006)}),i.c("design:paramtypes",[n.h,o.d,o.c,n.M,n.k])],t)})(p.a)},955:function(e,t,a){"use strict";var i=a(4),n=a(2),o=a(26),s=a(16),r=a(91),l=a(43),p=a(9),c=a(35),d=a(119);a.d(t,"a",(function(){return g}));var g=(function(e){function t(t,a,i){var n=e.call(this,t)||this;return n._router=a,n._storageSvc=i,n.pageList=[],n.pageCategoryList=[],n._currentSiteId="",n._currentUserId="",n._currentUserLevel="",n._pageNav=[],n.isBusy=!0,n.selectedPageObj={id:"",name:""},n.loadComplete=!1,n.filterPageName="",n.filterPageCategory="",n._elem.addClass("component-wrapper"),n._cmn.setInfo({navList:[],pageTitle:"app_nav.pages_content",topBarButtonList:[]}),n.isBusy=!0,n.loadComplete=!1,n._infoSubscription=n._cmn.getInfo().subscribe((function(e){n._currentUserId=e.username,n._currentUserLevel=e.currentUserLevel,e.currentSiteId!==n._currentSiteId?(n._currentSiteId=e.currentSiteId,n.loadComplete=!1,n.isBusy=!0,n.getPagesList()):(n.isBusy=!1,n.loadComplete=!0)})),n._dlg=t.get(c.a),n._modulePropDetailCmpFactory=n._resolver.resolveComponentFactory(d.a),n}return i.a(t,e),t.prototype.ngOnInit=function(){this._elem.closest(".cmp-container").addClass("full-expand")},t.prototype.ngOnDestroy=function(){this._infoSubscription.unsubscribe()},t.prototype.getDialogOptions=function(e,t){var a="dialog.no_title",i={moduleType:e,mode:t},n={title:"dialog.item_detail",expandableDialog:!0,dialogCssClass:"size-lg",componentFactory:this._modulePropDetailCmpFactory,useCustomButtons:!0,hasTopPanel:!0,noCloseButton:!0};return a=t===p.j.CREATE?"dialog.new_page":"dialog.page_detail",i.hasTemplateSelection=!0,n.title=a,n.dialogData=i,n},t.prototype.openPageProperties=function(e,t){var a=this,i=this.getDialogOptions("page",p.j.UPDATE);i.dialogData=Object.assign({},i.dialogData,{datatable:{updateRow:function(e){a.pageList=a.pageList.map((function(t){return t.id===e.id?e:t}))}}}),i.dialogData.selectedData=Object.assign.apply(Object,[{}].concat(t)),i.dialogData.hasPublishedDate="published_at"in t,this._dlg.open(i)},t.prototype.openPageDetail=function(e,t){$(e.currentTarget).closest(".page-item").hasClass("page-item")&&this._router.navigate(["/page-content/"+t.id])},t.prototype.getPagesList=function(){var e=this,t=Object.assign({},s.a.url.sites.getPages);t.url=t.url.replace(/(:id)/g,this._currentSiteId),this._req.doRequest(t).subscribe((function(t){var a=[];if(a=e._currentUserLevel===p.c.EDITORIAL?t.data.filter((function(t){if(t.permissions){var a=[];try{a=JSON.parse(t.permissions)}catch(e){a=[]}if(a.length>0)return a.indexOf(e._currentUserId)>-1}return!0})):t.data,a=a.filter((function(e){return e.is_active})),a.sort((function(e,t){return e.name<t.name?-1:1})),e.pageList=a,e.pageList.length>0){var i=[];e.pageList.forEach((function(e){e.hasOwnProperty("categories")&&i.push.apply(i,e.categories)})),e.pageCategoryList=$.unique(i)}e.isBusy=!1,e.loadComplete=!0}))},t.prototype.viewSubPage=function(e,t){t.children&&t.children.length>0&&(this.selectedPageObj.id=t.id,this.selectedPageObj.name=t.name,this.filterPageName="")},t.prototype.backToParent=function(){this.selectedPageObj.id="",this.selectedPageObj.name="",this.filterPageName=""},t=i.b([a.i(n._14)({selector:"[cmp-content-page-list]",template:a(1007)}),i.c("design:paramtypes",[n.h,o.c,l.a])],t)})(r.a)},975:function(e,t,a){"use strict";var i=a(4),n=a(2),o=a(951),s=a(120),r=a(119),l=a(955),p=a(954),c=a(168);a.d(t,"a",(function(){return d}));var d=(function(e){function t(t,a){var i=e.call(this,t)||this;return i._pageTitle="Pages",i._elem=$(a.nativeElement),i}return i.a(t,e),t.prototype.ngOnInit=function(){},t=i.b([a.i(n._14)({selector:"[cmp-pages-content]",template:a(952),entryComponents:[s.a,r.a,l.a,p.a,c.a]}),i.c("design:paramtypes",[n.h,n.M])],t)})(o.a)},986:function(e,t,a){"use strict";var i=a(4),n=a(2),o=a(26),s=a(987),r=a(975),l=a(955),p=a(421),c=a(954);a.d(t,"a",(function(){return d}));var d=(function(){function e(){}return e.routes=s.a,e=i.b([a.i(n.z)({declarations:[r.a,l.a,c.a],imports:[p.a,o.a.forChild(s.a)]})],e)})()},987:function(e,t,a){"use strict";var i=a(975),n=a(955),o=a(954);a.d(t,"a",(function(){return s}));var s=[{path:"",component:i.a,children:[{path:"",component:n.a},{path:":id",component:o.a}]}]}});