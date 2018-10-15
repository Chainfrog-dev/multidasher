import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';

import { AppComponent } from './app.component';
import { MultidasherNavComponent } from './multidasher-nav/multidasher-nav.component';
import { LayoutModule } from '@angular/cdk/layout';
import { MatToolbarModule, MatButtonModule, MatSidenavModule, MatIconModule, MatListModule, MatGridListModule, MatCardModule, MatMenuModule, MatTableModule, MatPaginatorModule, MatSortModule, MatInputModule, MatSelectModule, MatRadioModule } from '@angular/material';
import { MultidasherDashboardComponent } from './multidasher-dashboard/multidasher-dashboard.component';
import { MultidasherFormComponent } from './multidasher-form/multidasher-form.component';
import { ReactiveFormsModule } from '@angular/forms';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { RouterModule }   from '@angular/router';
import { MultidasherTableComponent } from './multidasher-table/multidasher-table.component';
import { DataService } from './data-feeds.service';
import { HttpClientModule } from '@angular/common/http';
import { FlexLayoutModule } from '@angular/flex-layout';
import { MatStepperModule } from '@angular/material/stepper';
import { CreateBlockchainComponent } from './create-blockchain/create-blockchain.component';

@NgModule({
  declarations: [
    AppComponent,
    MultidasherNavComponent,
    MultidasherDashboardComponent,
    MultidasherFormComponent,
    MultidasherTableComponent,
    CreateBlockchainComponent
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
    RouterModule.forRoot([
      {
        path: 'dashboard',
        component: MultidasherDashboardComponent
      },
      {
        path: 'table',
        component: MultidasherTableComponent
      },
      {
        path: 'form',
        component: MultidasherFormComponent
      }
    ]),

  ],
  providers: [DataService],
  bootstrap: [AppComponent]
})
export class AppModule { }
