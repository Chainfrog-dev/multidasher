import { Component, OnInit } from '@angular/core';
import { DataService } from '../data-feeds.service';
import { ActivatedRoute, Router, NavigationEnd } from '@angular/router';

export interface Recipient {
	name: string;
	description: string;
	asset: string;
	address: string
}

@Component({
  selector: 'app-multidasher-recipient',
  templateUrl: './multidasher-recipient.component.html',
  styleUrls: ['./multidasher-recipient.component.scss']
})
export class MultidasherRecipientComponent implements OnInit {
	blockchainId:String;
	recipients: Recipient[] = [];

	constructor(
		private route: ActivatedRoute,
		private dataService : DataService
		) { }

	ngOnInit() {
		this.blockchainId = this.route.snapshot.params['blockchainId'];
		this.getRecipients(this.blockchainId);
	}

	async getRecipients(nid : String) {
		const response = await this.dataService.getRecipients(nid).toPromise();
		console.log(response);
		for(let key in response['data']){
			let recipient : Recipient = {
				description: response['data'][key]['description'],
				name: response['data'][key]['name'],
				asset: response['data'][key]['asset'],
				address: response['data'][key]['address']
			}
			this.recipients.push(recipient);
		}

	}
}
