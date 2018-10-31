import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MultidasherRecipientComponent } from './multidasher-recipient.component';

describe('MultidasherRecipientComponent', () => {
  let component: MultidasherRecipientComponent;
  let fixture: ComponentFixture<MultidasherRecipientComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MultidasherRecipientComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MultidasherRecipientComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
