import { Component, OnInit } from '@angular/core';
import { DataService } from '../data-feeds.service';
import { ActivatedRoute, Router, NavigationEnd } from '@angular/router';

export interface Currency {
	id?: string;
	name: string;
	balance: string;
}
export interface Wallet {
	id: string;
	name: string;
	balance: Currency[];
	address: string;
}

@Component({
	selector: 'app-multidasher-wallets',
	templateUrl: './multidasher-wallets.component.html',
	styleUrls: ['./multidasher-wallets.component.scss']
})
export class MultidasherWalletsComponent implements OnInit {
	blockchainId:String;
	wallets: Wallet[] = [];

	constructor(
		private route: ActivatedRoute,
		private dataService : DataService
		) { }

	ngOnInit() {

		this.blockchainId = this.route.snapshot.params['blockchainId'];
		this.getWallets(this.blockchainId);
	}

	async getWallets(nid : String) {
		const response = await this.dataService.getWallets(nid).toPromise();
		for(let key in response['data']){
			let wallet : Wallet = {
				id: response['data'][key]['wallet_id'],
				name: response['data'][key]['name'],
				balance: response['data'][key]['balance'],
				address: response['data'][key]['address']
			}
			this.wallets.push(wallet);
		}

	}
}
