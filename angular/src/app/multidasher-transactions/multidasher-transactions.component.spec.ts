import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MultidasherTransactionsComponent } from './multidasher-transactions.component';

describe('MultidasherTransactionsComponent', () => {
  let component: MultidasherTransactionsComponent;
  let fixture: ComponentFixture<MultidasherTransactionsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MultidasherTransactionsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MultidasherTransactionsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
