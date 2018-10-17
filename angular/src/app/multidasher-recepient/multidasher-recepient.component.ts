import { Component, OnInit } from '@angular/core';
import { DataService } from '../data-feeds.service';
import { ActivatedRoute, Router, NavigationEnd } from '@angular/router';

export interface Recepient {
	name: string;
	description: string;
	asset: string;
	address: string
}

@Component({
  selector: 'app-multidasher-recepient',
  templateUrl: './multidasher-recepient.component.html',
  styleUrls: ['./multidasher-recepient.component.scss']
})
export class MultidasherRecepientComponent implements OnInit {
	blockchainId:String;
	recepients: Recepient[] = [];

	constructor(
		private route: ActivatedRoute,
		private dataService : DataService
		) { }

	ngOnInit() {
		this.blockchainId = this.route.snapshot.params['blockchainId'];
		this.getRecepients(this.blockchainId);
	}

	async getRecepients(nid : String) {
		const response = await this.dataService.getRecepients(nid).toPromise();
		console.log(response);
		for(let key in response['data']){
			let recepient : Recepient = {
				description: response['data'][key]['description'],
				name: response['data'][key]['name'],
				asset: response['data'][key]['asset'],
				address: response['data'][key]['address']
			}
			this.recepients.push(recepient);
		}

	}
}
