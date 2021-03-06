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
  blockchainController: FormGroup;
  masterJson: any;
  chainAddress: string;

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

    this.blockchainController = this.fb.group({
      chainAddress: ['', Validators.required],
      email: ['', Validators.required],
      firstName: ['', Validators.required],
      lastName: ['', Validators.required]
    });
  }

  async initiateBlockchain() {
    const initiateResult = await this.dataService.initiateRemoteBlockchain(this.blockchainName.value.blockchainName,this.blockchainName.value.blockchainIp,this.blockchainName.value.blockchainPort).toPromise();
    const createBlockchainDrupal = await this.dataService.updateBlockchains().toPromise();
    console.log(createBlockchainDrupal);
    const blockchainNid = await this.dataService.getBlockchainNid(this.blockchainName.value.blockchainName).toPromise();
    console.log(blockchainNid);
    const nid = blockchainNid['data']['result'];
    const createWalletDrual = await this.dataService.updateAddresses(nid).toPromise();
    const retrieveUrl = await this.dataService.getBlockchainMaster(this.blockchainName.value.blockchainName,'root').toPromise();
    console.log(retrieveUrl);
    const originalPublisher = retrieveUrl['data']['result'][0]['publishers'][0];
    const masterJson = await this.dataService.getMasterJson(this.blockchainName.value.blockchainName,'root',originalPublisher).toPromise();
    const masterAddress = await this.dataService.getMasterAddress(this.blockchainName.value.blockchainName).toPromise();
    this.masterJson = retrieveUrl['data']['result'][0]['data']['json'];
    this.chainAddress = masterAddress['data']['result'][0]['address'];
  }

  async registerBlockchain() {
    const registerBlockchain = await this.dataService.registerBlockchain(this.masterJson['resource-url'], this.chainAddress, this.blockchainController.value).toPromise();
    console.log(registerBlockchain);
  }

}
