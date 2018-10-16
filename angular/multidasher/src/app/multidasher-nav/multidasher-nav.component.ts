import { Component, OnInit } from '@angular/core';
import { BreakpointObserver, Breakpoints, BreakpointState } from '@angular/cdk/layout';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { ActivatedRoute, Router, NavigationEnd } from '@angular/router';   
import { DataService } from '../data-feeds.service';

export interface Blockchain {
  name: string;
  id: string;
  status: string;
}

@Component({
	selector: 'app-multidasher-nav',
	templateUrl: './multidasher-nav.component.html',
	styleUrls: ['./multidasher-nav.component.scss']
})
export class MultidasherNavComponent implements OnInit {
	activeBlockchain: String;
	isHandset$: Observable<boolean> = this.breakpointObserver.observe(Breakpoints.Handset).pipe(map(result => result.matches));
	blockchainArray: Blockchain[] = [];
	constructor(
		private breakpointObserver: BreakpointObserver,
		private _route: ActivatedRoute,
		private _router: Router,
		private dataService: DataService
	) {}

	ngOnInit() {
		this._router.events.forEach((event) => {
			if(event instanceof NavigationEnd) {
				if(this._route.snapshot['_routerState']['url'].includes('/blockchain/')){
					var args = this._route.snapshot['_routerState']['url'].split('/');
					this.activeBlockchain = args[2];
				}else {
					this.activeBlockchain = null;
				}
			}
		});
		this.loadBlockchains();
	}

	async loadBlockchains() {
    const result = await this.dataService.getBlockchains().toPromise();
	    for(let value of result['data']){
	      let blockchain : Blockchain = {
	          'name' : value.name,
	          'id' : value.id,
	          'status' : '',
	        }
	      this.blockchainArray.push(blockchain);
		}
	}
}
	