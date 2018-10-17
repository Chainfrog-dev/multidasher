import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CreateBlockchainComponent } from './create-blockchain.component';

describe('CreateBlockchainComponent', () => {
  let component: CreateBlockchainComponent;
  let fixture: ComponentFixture<CreateBlockchainComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CreateBlockchainComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CreateBlockchainComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
