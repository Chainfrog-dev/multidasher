import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { JoinBlockchainComponent } from './join-blockchain.component';

describe('JoinBlockchainComponent', () => {
  let component: JoinBlockchainComponent;
  let fixture: ComponentFixture<JoinBlockchainComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ JoinBlockchainComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(JoinBlockchainComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
