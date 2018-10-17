import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MultidasherWalletsComponent } from './multidasher-wallets.component';

describe('MultidasherWalletsComponent', () => {
  let component: MultidasherWalletsComponent;
  let fixture: ComponentFixture<MultidasherWalletsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MultidasherWalletsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MultidasherWalletsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
