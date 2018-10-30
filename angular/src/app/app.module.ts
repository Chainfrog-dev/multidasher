import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';

import { AppComponent } from './app.component';
import { MultidasherNavComponent } from './multidasher-nav/multidasher-nav.component';
import { LayoutModule } from '@angular/cdk/layout';
import { MatToolbarModule, MatButtonModule, MatSidenavModule, MatIconModule, MatListModule, MatGridListModule, MatCardModule, MatMenuModule, MatTableModule, MatPaginatorModule, MatSortModule, MatInputModule, MatSelectModule, MatRadioModule } from '@angular/material';
import { MultidasherDashboardComponent } from './multidasher-dashboard/multidasher-dashboard.component';
import { ReactiveFormsModule } from '@angular/forms';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { RouterModule }   from '@angular/router';
import { MultidasherTableComponent } from './multidasher-table/multidasher-table.component';
import { DataService } from './data-feeds.service';
import { AuthService } from './auth.service';
import { HttpClientModule } from '@angular/common/http';
import { FlexLayoutModule } from '@angular/flex-layout';
import { MatStepperModule } from '@angular/material/stepper';
import { CreateBlockchainComponent } from './create-blockchain/create-blockchain.component';
import { MultidasherInfoComponent } from './multidasher-info/multidasher-info.component';
import { MultidasherWalletsComponent } from './multidasher-wallets/multidasher-wallets.component';
import { CreateAddressComponent } from './create-address/create-address.component';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MultidasherAssetsComponent } from './multidasher-assets/multidasher-assets.component';
import { CreateAssetComponent } from './create-asset/create-asset.component';
import { MultidasherTransactionsComponent } from './multidasher-transactions/multidasher-transactions.component';
import { MultidasherRecepientComponent } from './multidasher-recepient/multidasher-recepient.component';
import { CreateRecepientComponent } from './create-recepient/create-recepient.component';
import { JoinBlockchainComponent } from './join-blockchain/join-blockchain.component';
import { SendAssetComponent } from './send-asset/send-asset.component';
import { CookieService } from 'ngx-cookie-service';
import { UserLoginComponent } from './user-login/user-login.component';

@NgModule({
  declarations: [
    AppComponent,
    MultidasherNavComponent,
    MultidasherDashboardComponent,
    MultidasherTableComponent,
    CreateBlockchainComponent,
    MultidasherInfoComponent,
    MultidasherWalletsComponent,
    CreateAddressComponent,
    MultidasherAssetsComponent,
    CreateAssetComponent,
    MultidasherTransactionsComponent,
    MultidasherRecepientComponent,
    CreateRecepientComponent,
    JoinBlockchainComponent,
    SendAssetComponent,
    UserLoginComponent
  ],
  imports: [
    BrowserModule,
    LayoutModule,
    MatToolbarModule,
    MatButtonModule,
    MatSidenavModule,
    MatIconModule,
    MatListModule,
    MatGridListModule,
    MatCardModule,
    MatMenuModule,
    MatTableModule,
    MatPaginatorModule,
    MatSortModule,
    MatInputModule,
    MatSelectModule,
    MatRadioModule,
    ReactiveFormsModule,
    BrowserAnimationsModule,
    HttpClientModule,
    FlexLayoutModule,
    MatStepperModule,
    MatCheckboxModule,
    RouterModule.forRoot([ 
      {
        path: '',
        component: UserLoginComponent
      },
      {
        path: 'blockchain/:blockchainId/view-info',
        component: MultidasherInfoComponent
      },
      {
        path: 'blockchain/:blockchainId/wallets',
        component: MultidasherWalletsComponent
      },
      {
        path: 'blockchain/:blockchainId/assets',
        component: MultidasherAssetsComponent
      },
      {
        path: 'blockchain/:blockchainId/recepients',
        component: MultidasherRecepientComponent
      },
      {
        path: 'blockchain/:blockchainId/assets/:asset',
        component: MultidasherTransactionsComponent
      }
    ]),

  ],
  providers: [DataService, AuthService, CookieService],
  bootstrap: [AppComponent]
})
export class AppModule { }
