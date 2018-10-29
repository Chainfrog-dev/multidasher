import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router, NavigationEnd } from '@angular/router';
import { DataService } from '../data-feeds.service';

@Component({
  selector: 'app-multidasher-info',
  templateUrl: './multidasher-info.component.html',
  styleUrls: ['./multidasher-info.component.scss']
})
export class MultidasherInfoComponent implements OnInit {
	blockchainId:String;
  informationArray: String[];

  // Preparing the mixed Objects / Values of javascript Object to be converted to Array for iteration
  infoProperties : any;
  infoArray : any = [];

  constructor(
  	private route: ActivatedRoute,
  	private dataService : DataService
  ) {}

  ngOnInit() {
  	console.log('loading');
  	this.blockchainId = this.route.snapshot.params['blockchainId'];
  	this.loadInfo(this.blockchainId);
  }

  async loadInfo(blockchainId){
    const response = await this.dataService.getBlockchainInfo(blockchainId).toPromise();
    this.infoProperties = Object.keys(response['data']['info']);
    // A few manipulations where data is uniform enough to be iterated
    for (let key of this.infoProperties) { 
        this.infoArray.push({key: key,value: response['data']['info'][key]});
    }

    console.log(this.infoArray);
    return response;
  }
}
