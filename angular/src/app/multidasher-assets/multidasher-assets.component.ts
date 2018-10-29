import { Component, OnInit } from '@angular/core';
import { DataService } from '../data-feeds.service';
import { ActivatedRoute, Router, NavigationEnd } from '@angular/router';

export interface Asset {
	description: string;
	name: string;
}

@Component({
  selector: 'app-multidasher-assets',
  templateUrl: './multidasher-assets.component.html',
  styleUrls: ['./multidasher-assets.component.scss']
})
export class MultidasherAssetsComponent implements OnInit {
	blockchainId:String;
	assets: Asset[] = [];

	constructor(
		private route: ActivatedRoute,
		private dataService : DataService
		) { }

	ngOnInit() {
		this.blockchainId = this.route.snapshot.params['blockchainId'];
		this.getAssets(this.blockchainId);
	}

	async getAssets(nid : String) {
		const response = await this.dataService.getAssets(nid).toPromise();
		console.log(response);
		for(let key in response['data']){
			let asset : Asset = {
				'description': response['data'][key]['description'],
				'name': response['data'][key]['name'],
			}
      if (this.assets.filter(item=> item.name == asset.name).length == 0){
				this.assets.push(asset);
      }
		}

	}
}
