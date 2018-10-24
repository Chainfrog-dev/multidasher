import { Component, OnInit } from '@angular/core';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import { DataService } from '../data-feeds.service';
import { ActivatedRoute, Router, NavigationEnd } from '@angular/router';    

@Component({
  selector: 'app-join-blockchain',
  templateUrl: './join-blockchain.component.html',
  styleUrls: ['./join-blockchain.component.scss']
})

export class JoinBlockchainComponent implements OnInit {

  blockchainId : String;
  blockchainName: FormGroup;
  blockchainParams: FormGroup;

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private dataService : DataService
  ) { }

  ngOnInit() {
    this.blockchainId = this.route.snapshot.params['blockchainId'];
    this.blockchainName = this.fb.group({
      blockchainName: ['', Validators.required],
      blockchainIp: ['', Validators.required],
      blockchainPort: ['', Validators.required]
    });
  }

  async initiateBlockchain(blockchainName, ip, port) {
    const initiateResult = await this.dataService.initiateRemoteBlockchain(this.blockchainName.value.blockchainName,this.blockchainName.value.blockchainIp,this.blockchainName.value).toPromise();
    console.log(initiateResult);
    const retrieveUrl = await this.dataService.getBlockchainMaster(this.blockchainName.value.blockchainName,'root').toPromise();
    console.log(retrieveUrl);
  }
}
