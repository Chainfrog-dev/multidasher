import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CreateAssetComponent } from './create-asset.component';

describe('CreateAssetComponent', () => {
  let component: CreateAssetComponent;
  let fixture: ComponentFixture<CreateAssetComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CreateAssetComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CreateAssetComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
